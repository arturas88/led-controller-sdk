<?php

declare(strict_types=1);

namespace LEDController\Tests\Unit;

use LEDController\Exception\FileException;
use LEDController\LEDController;
use LEDController\Manager\FileManager;
use LEDController\Response;
use PHPUnit\Framework\TestCase;

class FileManagerTest extends TestCase
{
    private FileManager $fileManager;
    private LEDController $controller;

    protected function setUp(): void
    {
        $this->controller = $this->createMock(LEDController::class);
        $this->fileManager = new FileManager($this->controller);
    }

    public function testDeleteFileSuccess(): void
    {
        // Arrange
        $filename = 'test.txt';
        $cardId = 1;

        $this->controller->method('getConfig')
            ->willReturn(['cardId' => $cardId]);

        $this->controller->method('isConnected')
            ->willReturn(true);

        $response = $this->createMock(Response::class);
        $response->method('isSuccess')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('sendPacket')
            ->willReturn($response);

        // Act
        $this->fileManager->initialize();
        $result = $this->fileManager->delete($filename);

        // Assert
        $this->assertSame($this->fileManager, $result);
    }

    public function testDeleteFileFailure(): void
    {
        // Arrange
        $filename = 'test.txt';
        $cardId = 1;

        $this->controller->method('getConfig')
            ->willReturn(['cardId' => $cardId]);

        $this->controller->method('isConnected')
            ->willReturn(true);

        $response = $this->createMock(Response::class);
        $response->method('isSuccess')
            ->willReturn(false);
        $response->method('getReturnCodeMessage')
            ->willReturn('File not found');

        $this->controller->method('sendPacket')
            ->willReturn($response);

        // Act & Assert
        $this->fileManager->initialize();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("Failed to delete file 'test.txt': File not found");

        $this->fileManager->delete($filename);
    }

    public function testGetFreeSpaceSuccess(): void
    {
        // Arrange
        $cardId = 1;
        $expectedSpace = 1024000; // 1MB in bytes

        $this->controller->method('getConfig')
            ->willReturn(['cardId' => $cardId]);

        $this->controller->method('isConnected')
            ->willReturn(true);

        $response = $this->createMock(Response::class);
        $response->method('isSuccess')
            ->willReturn(true);
        $response->method('getFreeSpace')
            ->willReturn($expectedSpace);

        $this->controller->expects($this->once())
            ->method('sendPacket')
            ->willReturn($response);

        // Act
        $this->fileManager->initialize();
        $result = $this->fileManager->getFreeSpace();

        // Assert
        $this->assertEquals($expectedSpace, $result);
    }

    public function testUploadThrowsException(): void
    {
        // Arrange
        $this->controller->method('isConnected')
            ->willReturn(true);

        // Act & Assert
        $this->fileManager->initialize();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File upload is not currently supported. Only file deletion and disk space queries are available.');

        $this->fileManager->upload('remote.txt', 'local.txt');
    }

    public function testDownloadThrowsException(): void
    {
        // Arrange
        $this->controller->method('isConnected')
            ->willReturn(true);

        // Act & Assert
        $this->fileManager->initialize();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File download is not currently supported. Only file deletion and disk space queries are available.');

        $this->fileManager->download('remote.txt', 'local.txt');
    }

    public function testListFilesThrowsException(): void
    {
        // Arrange
        $this->controller->method('isConnected')
            ->willReturn(true);

        // Act & Assert
        $this->fileManager->initialize();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File listing is not currently supported. Only file deletion and disk space queries are available.');

        $this->fileManager->listFiles();
    }

    public function testVerifyThrowsException(): void
    {
        // Arrange
        $this->controller->method('isConnected')
            ->willReturn(true);

        // Act & Assert
        $this->fileManager->initialize();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File verification is not currently supported. Only file deletion and disk space queries are available.');

        $this->fileManager->verify('remote.txt', 'local.txt');
    }

    public function testNotReadyThrowsException(): void
    {
        // Act & Assert
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('FileManager not ready for operations');

        $this->fileManager->delete('test.txt');
    }
}
