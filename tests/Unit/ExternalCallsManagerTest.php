<?php

declare(strict_types=1);

namespace LEDController\Tests\Unit;

use LEDController\Exception\ExternalCallsException;
use LEDController\LEDController;
use LEDController\Manager\ExternalCallsManager;
use LEDController\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExternalCallsManagerTest extends TestCase
{
    private ExternalCallsManager $manager;

    /** @var LEDController&MockObject */
    private $controller;

    protected function setUp(): void
    {
        // Create a mock controller with test configuration
        $this->controller = $this->createMock(LEDController::class);
        $this->controller->method('getConfig')->willReturn([
            'cardId' => 1,
            'displayWidth' => 128,
            'displayHeight' => 32,
            'display' => [
                'defaultWidth' => 128,
                'defaultHeight' => 32,
            ],
        ]);

        $this->manager = new ExternalCallsManager($this->controller);
    }

    public function testGetDisplayDimensions()
    {
        $dimensions = $this->manager->getDisplayDimensions();

        self::assertSame(128, $dimensions['width']);
        self::assertSame(32, $dimensions['height']);
    }

    public function testGetDisplayDimensionsFallback()
    {
        // Test with controller that has no display config
        /** @var LEDController&MockObject */
        $controller = $this->createMock(LEDController::class);
        $controller->method('getConfig')->willReturn(['cardId' => 1]);

        $manager = new ExternalCallsManager($controller);
        $dimensions = $manager->getDisplayDimensions();

        self::assertSame(128, $dimensions['width']);
        self::assertSame(32, $dimensions['height']);
    }

    public function testCreateTableLayout2x2()
    {
        $windows = $this->manager->createTableLayout(2, 2);

        self::assertCount(4, $windows);

        // Check first window (top-left)
        self::assertSame(0, $windows[0]['id']);
        self::assertSame(0, $windows[0]['x']);
        self::assertSame(0, $windows[0]['y']);
        self::assertSame(64, $windows[0]['width']);
        self::assertSame(16, $windows[0]['height']);

        // Check second window (top-right)
        self::assertSame(1, $windows[1]['id']);
        self::assertSame(64, $windows[1]['x']);
        self::assertSame(0, $windows[1]['y']);
        self::assertSame(64, $windows[1]['width']); // Last column uses remaining space
        self::assertSame(16, $windows[1]['height']);

        // Check third window (bottom-left)
        self::assertSame(2, $windows[2]['id']);
        self::assertSame(0, $windows[2]['x']);
        self::assertSame(16, $windows[2]['y']);
        self::assertSame(64, $windows[2]['width']);
        self::assertSame(16, $windows[2]['height']); // Last row uses remaining space

        // Check fourth window (bottom-right)
        self::assertSame(3, $windows[3]['id']);
        self::assertSame(64, $windows[3]['x']);
        self::assertSame(16, $windows[3]['y']);
        self::assertSame(64, $windows[3]['width']);
        self::assertSame(16, $windows[3]['height']);
    }

    public function testCreateTableLayout4x2()
    {
        $windows = $this->manager->createTableLayout(4, 2);

        self::assertCount(8, $windows);

        // Check window dimensions
        self::assertSame(32, $windows[0]['width']); // 128 / 4 = 32
        self::assertSame(16, $windows[0]['height']); // 32 / 2 = 16

        // Check last column uses remaining space
        self::assertSame(96, $windows[3]['x']); // 3 * 32 = 96
        self::assertSame(32, $windows[3]['width']); // 128 - 96 = 32
    }

    public function testCreateTableLayoutWithCustomDimensions()
    {
        $customDimensions = ['width' => 192, 'height' => 64];
        $windows = $this->manager->createTableLayout(2, 2, $customDimensions); // Changed to 2x2 to stay under 8 windows

        self::assertCount(4, $windows);

        // Check window dimensions
        self::assertSame(96, $windows[0]['width']); // 192 / 2 = 96
        self::assertSame(32, $windows[0]['height']); // 64 / 2 = 32
    }

    public function testCreateTableLayoutInvalidColumns()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Columns must be between 1 and 8');

        $this->manager->createTableLayout(0, 2);
    }

    public function testCreateTableLayoutInvalidRows()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Rows must be between 1 and 8');

        $this->manager->createTableLayout(2, 0);
    }

    public function testCreateTableLayoutTooManyWindows()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Total windows (9) cannot exceed 8');

        $this->manager->createTableLayout(3, 3);
    }

    public function testCreateTableLayoutWindowTooSmall()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Window size too small: 4x32 pixels. Minimum is 8x8.');

        // 32x32 display with 8x1 grid = 8 windows of 4x32 pixels
        $this->manager->createTableLayout(8, 1, ['width' => 32, 'height' => 32]);
    }

    public function testApplyTableLayout()
    {
        // Mock the sendPacket method to avoid actual communication
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('isSuccess')->willReturn(true);

        $this->controller->expects(self::once())
            ->method('sendPacket')
            ->willReturn($mockResponse);

        $windows = $this->manager->applyTableLayout(2, 2);

        self::assertCount(4, $windows);
        self::assertTrue($this->manager->getState()['splitScreenMode']);
    }

    public function testValidateWindow()
    {
        $validWindow = [
            'x' => 0,
            'y' => 0,
            'width' => 64,
            'height' => 32,
        ];

        // Mock the sendPacket method
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('isSuccess')->willReturn(true);
        $this->controller->method('sendPacket')->willReturn($mockResponse);

        // This should not throw an exception
        $result = $this->manager->splitScreen([$validWindow]);

        // Add assertions to make the test not risky
        self::assertTrue($this->manager->getState()['splitScreenMode']);
        self::assertInstanceOf(ExternalCallsManager::class, $result);
    }

    public function testValidateWindowMissingProperties()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Window must have x, y, width, and height');

        $invalidWindow = [
            'x' => 0,
            'y' => 0,
            // Missing width and height
        ];

        $this->manager->splitScreen([$invalidWindow]);
    }
}
