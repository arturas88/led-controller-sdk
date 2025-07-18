<?php

declare(strict_types=1);

namespace LEDController\Manager;

use LEDController\Exception\FileException;
use LEDController\Interface\FileManagerInterface;
use LEDController\LEDController;
use LEDController\PacketBuilder;

/**
 * File manager for basic file operations on the controller.
 *
 * Note: This implementation only supports file deletion and disk space queries
 * as these are the only file operations currently implemented in the protocol.
 */
class FileManager implements FileManagerInterface
{
    private LEDController $controller;

    /** @var null|callable */
    private $progressCallback;

    private bool $initialized = false;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize the manager.
     */
    public function initialize(): void
    {
        $this->initialized = true;
    }

    /**
     * Get the controller instance.
     */
    public function getController(): LEDController
    {
        return $this->controller;
    }

    /**
     * Check if manager is ready for operations.
     */
    public function isReady(): bool
    {
        return $this->initialized && $this->controller->isConnected();
    }

    /**
     * Clean up resources.
     */
    public function cleanup(): void
    {
        $this->progressCallback = null;
        $this->initialized = false;
    }

    /**
     * Set progress callback.
     *
     * Note: Progress callbacks are not used in current implementation
     * as only basic file operations are supported.
     */
    public function onProgress(callable $callback): self
    {
        $this->progressCallback = $callback;

        return $this;
    }

    /**
     * Upload a file to the controller.
     *
     * @throws FileException This operation is not currently supported
     */
    public function upload(string $remoteFilename, string $localPath): self
    {
        throw new FileException('File upload is not currently supported. Only file deletion and disk space queries are available.');
    }

    /**
     * Download a file from the controller.
     *
     * @throws FileException This operation is not currently supported
     */
    public function download(string $remoteFilename, string $localPath): self
    {
        throw new FileException('File download is not currently supported. Only file deletion and disk space queries are available.');
    }

    /**
     * Delete a file from the controller.
     */
    public function delete(string $filename): self
    {
        if (!$this->isReady()) {
            throw new FileException('FileManager not ready for operations');
        }

        $packet = PacketBuilder::createFileRemovePacket($this->controller->getConfig()['cardId'], $filename);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileException("Failed to delete file '{$filename}': " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Get available disk space.
     */
    public function getFreeSpace(): int
    {
        if (!$this->isReady()) {
            throw new FileException('FileManager not ready for operations');
        }

        $packet = PacketBuilder::createDiskSpaceQueryPacket($this->controller->getConfig()['cardId']);
        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new FileException('Failed to get disk space: ' . $response->getReturnCodeMessage());
        }

        return $response->getFreeSpace();
    }

    /**
     * List files in a directory.
     *
     * @throws FileException This operation is not currently supported
     */
    public function listFiles(string $directory = '', string $pattern = '*'): array
    {
        throw new FileException('File listing is not currently supported. Only file deletion and disk space queries are available.');
    }

    /**
     * Verify file integrity after upload.
     *
     * @throws FileException This operation is not currently supported
     */
    public function verify(string $remoteFilename, string $localPath): bool
    {
        throw new FileException('File verification is not currently supported. Only file deletion and disk space queries are available.');
    }
}
