<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\Interface\FileManagerInterface;
use LEDController\PacketBuilder;
use LEDController\Packet;
use LEDController\Exception\FileNotFoundException;
use LEDController\Exception\FileException;
use LEDController\Exception\FileTransferException;

/**
 * File manager for uploading, downloading, and managing files on the controller
 */
class FileManager implements FileManagerInterface
{
    private LEDController $controller;
    /** @var callable|null */
    private $progressCallback = null;
    private int $blockSize = 512; // Maximum bytes per packet
    private bool $initialized = false;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize the manager
     */
    public function initialize(): void
    {
        $this->initialized = true;
    }

    /**
     * Get the controller instance
     */
    public function getController(): LEDController
    {
        return $this->controller;
    }

    /**
     * Check if manager is ready for operations
     */
    public function isReady(): bool
    {
        return $this->initialized && $this->controller->isConnected();
    }

    /**
     * Clean up resources
     */
    public function cleanup(): void
    {
        $this->progressCallback = null;
        $this->initialized = false;
    }

    /**
     * Set progress callback
     */
    public function onProgress(callable $callback): self
    {
        $this->progressCallback = $callback;
        return $this;
    }

    /**
     * Upload a file to the controller
     */
    public function upload(string $remoteFilename, string $localPath): self
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        if (!file_exists($localPath)) {
            throw new FileNotFoundException("Local file not found: $localPath");
        }

        $fileData = file_get_contents($localPath);
        if ($fileData === false) {
            throw new FileException("Failed to read local file: $localPath");
        }

        $fileSize = strlen($fileData);
        $fileTime = filemtime($localPath);

        // Use quick upload for better performance
        $this->quickUpload($remoteFilename, $fileData, $fileTime);

        return $this;
    }

    /**
     * Download a file from the controller
     */
    public function download(string $remoteFilename, string $localPath): self
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        $this->quickDownload($remoteFilename, $localPath);

        return $this;
    }

    /**
     * Delete a file from the controller
     */
    public function delete(string $filename): self
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        $packet = PacketBuilder::createDeleteFilePacket($this->controller->getConfig()['cardId'], $filename);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileException("Failed to delete file '$filename': " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Get available disk space
     */
    public function getFreeSpace(): int
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        $packet = PacketBuilder::createGetDiskSpacePacket($this->controller->getConfig()['cardId']);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileException("Failed to get disk space: " . $response->getReturnCodeMessage());
        }

        return $response->getFreeSpace();
    }

    /**
     * List files in a directory
     */
    public function listFiles(string $directory = ''): array
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        $packet = PacketBuilder::createListFilesPacket($this->controller->getConfig()['cardId'], $directory);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileException("Failed to list files: " . $response->getReturnCodeMessage());
        }

        return $response->getFileList();
    }

    /**
     * Verify file integrity after upload
     */
    public function verify(string $remoteFilename, string $localPath): bool
    {
        if (!$this->isReady()) {
            throw new FileException("FileManager not ready for operations");
        }

        if (!file_exists($localPath)) {
            throw new FileNotFoundException("Local file not found: $localPath");
        }

        // Get remote file info
        $packet = PacketBuilder::createGetFileInfoPacket($this->controller->getConfig()['cardId'], $remoteFilename);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            return false;
        }

        $localSize = filesize($localPath);
        $remoteSize = $response->getFileSize();

        return $localSize === $remoteSize;
    }

    /**
     * Private method for quick upload
     */
    private function quickUpload(string $filename, string $data, int $fileTime): void
    {
        $fileSize = strlen($data);
        $cardId = $this->controller->getConfig()['cardId'];

        // Open file for writing
        $packet = PacketBuilder::createOpenFilePacket($cardId, $filename, $fileSize, $fileTime);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileTransferException("Failed to open file for writing: " . $response->getReturnCodeMessage());
        }

        // Send data in blocks
        $bytesRemaining = $fileSize;
        $offset = 0;

        while ($bytesRemaining > 0) {
            $blockSize = min($this->blockSize, $bytesRemaining);
            $blockData = substr($data, $offset, $blockSize);

            $packet = PacketBuilder::createWriteFileDataPacket($cardId, $blockData);
            $response = $this->controller->sendPacket($packet);

            if (!$response->isSuccess()) {
                throw new FileTransferException("Failed to write file block: " . $response->getReturnCodeMessage());
            }

            $offset += $blockSize;
            $bytesRemaining -= $blockSize;

            // Call progress callback if set
            if ($this->progressCallback) {
                $progress = ($offset / $fileSize) * 100;
                call_user_func($this->progressCallback, $progress, $offset, $fileSize);
            }
        }

        // Close file
        $packet = PacketBuilder::createCloseFilePacket($cardId);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileTransferException("Failed to close file: " . $response->getReturnCodeMessage());
        }
    }

    /**
     * Private method for quick download
     */
    private function quickDownload(string $filename, string $localPath): void
    {
        $cardId = $this->controller->getConfig()['cardId'];

        // Open file for reading
        $packet = PacketBuilder::createOpenFileForReadPacket($cardId, $filename);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileTransferException("Failed to open file for reading: " . $response->getReturnCodeMessage());
        }

        $fileSize = $response->getFileSize();
        $fileData = '';
        $bytesRead = 0;

        // Read data in blocks
        while ($bytesRead < $fileSize) {
            $blockSize = min($this->blockSize, $fileSize - $bytesRead);

            $packet = PacketBuilder::createReadFileDataPacket($cardId, $blockSize);
            $response = $this->controller->sendPacket($packet);

            if (!$response->isSuccess()) {
                throw new FileTransferException("Failed to read file data: " . $response->getReturnCodeMessage());
            }

            $blockData = $response->getFileData();
            $fileData .= $blockData;
            $bytesRead += strlen($blockData);

            // Call progress callback if set
            if ($this->progressCallback) {
                $progress = ($bytesRead / $fileSize) * 100;
                call_user_func($this->progressCallback, $progress, $bytesRead, $fileSize);
            }
        }

        // Close file
        $packet = PacketBuilder::createCloseFilePacket($cardId);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileTransferException("Failed to close file: " . $response->getReturnCodeMessage());
        }

        // Save to local file
        $localDir = dirname($localPath);
        if (!is_dir($localDir)) {
            if (!mkdir($localDir, 0755, true)) {
                throw new FileException("Failed to create directory: $localDir");
            }
        }

        if (file_put_contents($localPath, $fileData) === false) {
            throw new FileException("Failed to save file: $localPath");
        }
    }
}
