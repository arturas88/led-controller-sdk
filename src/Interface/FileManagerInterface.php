<?php

declare(strict_types=1);

namespace LEDController\Interface;

/**
 * Interface for file management operations.
 */
interface FileManagerInterface extends ManagerInterface
{
    /**
     * Upload a file to the controller.
     */
    public function upload(string $remoteFilename, string $localPath): self;

    /**
     * Download a file from the controller.
     */
    public function download(string $remoteFilename, string $localPath): self;

    /**
     * Delete a file from the controller.
     */
    public function delete(string $filename): self;

    /**
     * Get available disk space.
     */
    public function getFreeSpace(): int;

    /**
     * List files in directory.
     *
     * @param string $directory Directory path
     * @param string $pattern File pattern (optional)
     *
     * @return array<int, string> List of file paths
     */
    public function listFiles(string $directory, string $pattern = '*'): array;

    /**
     * Set progress callback for file operations.
     */
    public function onProgress(callable $callback): self;

    /**
     * Verify file integrity after upload.
     */
    public function verify(string $remoteFilename, string $localPath): bool;
}
