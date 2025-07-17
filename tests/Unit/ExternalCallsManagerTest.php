<?php

namespace LEDController\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use LEDController\Manager\ExternalCallsManager;
use LEDController\LEDController;
use LEDController\Exception\ExternalCallsException;
use LEDController\Response;

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
                'defaultHeight' => 32
            ]
        ]);

        $this->manager = new ExternalCallsManager($this->controller);
    }

    public function testGetDisplayDimensions()
    {
        $dimensions = $this->manager->getDisplayDimensions();

        $this->assertEquals(128, $dimensions['width']);
        $this->assertEquals(32, $dimensions['height']);
    }

    public function testGetDisplayDimensionsFallback()
    {
        // Test with controller that has no display config
        /** @var LEDController&MockObject */
        $controller = $this->createMock(LEDController::class);
        $controller->method('getConfig')->willReturn(['cardId' => 1]);

        $manager = new ExternalCallsManager($controller);
        $dimensions = $manager->getDisplayDimensions();

        $this->assertEquals(128, $dimensions['width']);
        $this->assertEquals(32, $dimensions['height']);
    }

    public function testCreateTableLayout2x2()
    {
        $windows = $this->manager->createTableLayout(2, 2);

        $this->assertCount(4, $windows);

        // Check first window (top-left)
        $this->assertEquals(0, $windows[0]['id']);
        $this->assertEquals(0, $windows[0]['x']);
        $this->assertEquals(0, $windows[0]['y']);
        $this->assertEquals(64, $windows[0]['width']);
        $this->assertEquals(16, $windows[0]['height']);

        // Check second window (top-right)
        $this->assertEquals(1, $windows[1]['id']);
        $this->assertEquals(64, $windows[1]['x']);
        $this->assertEquals(0, $windows[1]['y']);
        $this->assertEquals(64, $windows[1]['width']); // Last column uses remaining space
        $this->assertEquals(16, $windows[1]['height']);

        // Check third window (bottom-left)
        $this->assertEquals(2, $windows[2]['id']);
        $this->assertEquals(0, $windows[2]['x']);
        $this->assertEquals(16, $windows[2]['y']);
        $this->assertEquals(64, $windows[2]['width']);
        $this->assertEquals(16, $windows[2]['height']); // Last row uses remaining space

        // Check fourth window (bottom-right)
        $this->assertEquals(3, $windows[3]['id']);
        $this->assertEquals(64, $windows[3]['x']);
        $this->assertEquals(16, $windows[3]['y']);
        $this->assertEquals(64, $windows[3]['width']);
        $this->assertEquals(16, $windows[3]['height']);
    }

    public function testCreateTableLayout4x2()
    {
        $windows = $this->manager->createTableLayout(4, 2);

        $this->assertCount(8, $windows);

        // Check window dimensions
        $this->assertEquals(32, $windows[0]['width']); // 128 / 4 = 32
        $this->assertEquals(16, $windows[0]['height']); // 32 / 2 = 16

        // Check last column uses remaining space
        $this->assertEquals(96, $windows[3]['x']); // 3 * 32 = 96
        $this->assertEquals(32, $windows[3]['width']); // 128 - 96 = 32
    }

    public function testCreateTableLayoutWithCustomDimensions()
    {
        $customDimensions = ['width' => 192, 'height' => 64];
        $windows = $this->manager->createTableLayout(2, 2, $customDimensions); // Changed to 2x2 to stay under 8 windows

        $this->assertCount(4, $windows);

        // Check window dimensions
        $this->assertEquals(96, $windows[0]['width']); // 192 / 2 = 96
        $this->assertEquals(32, $windows[0]['height']); // 64 / 2 = 32
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

        $this->controller->expects($this->once())
            ->method('sendPacket')
            ->willReturn($mockResponse);

        $windows = $this->manager->applyTableLayout(2, 2);

        $this->assertCount(4, $windows);
        $this->assertTrue($this->manager->getState()['splitScreenMode']);
    }

    public function testValidateWindow()
    {
        $validWindow = [
            'x' => 0,
            'y' => 0,
            'width' => 64,
            'height' => 32
        ];

        // Mock the sendPacket method
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('isSuccess')->willReturn(true);
        $this->controller->method('sendPacket')->willReturn($mockResponse);

        // This should not throw an exception
        $result = $this->manager->splitScreen([$validWindow]);

        // Add assertions to make the test not risky
        $this->assertTrue($this->manager->getState()['splitScreenMode']);
        $this->assertInstanceOf(ExternalCallsManager::class, $result);
    }

    public function testValidateWindowMissingProperties()
    {
        $this->expectException(ExternalCallsException::class);
        $this->expectExceptionMessage('Window must have x, y, width, and height');

        $invalidWindow = [
            'x' => 0,
            'y' => 0
            // Missing width and height
        ];

        $this->manager->splitScreen([$invalidWindow]);
    }
}
