<?php

namespace LEDController\Tests\Unit;

use LEDController\Enum\Color;
use LEDController\Enum\FontSize;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\VerticalAlignment;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;
use PHPUnit\Framework\TestCase;

class LEDControllerTest extends TestCase
{
    public function testFontSizeEnum()
    {
        $this->assertEquals(8, FontSize::FONT_8->getPixelSize());
        $this->assertEquals(12, FontSize::FONT_12->getPixelSize());
        $this->assertEquals(16, FontSize::FONT_16->getPixelSize());
        $this->assertEquals(24, FontSize::FONT_24->getPixelSize());
        $this->assertEquals(32, FontSize::FONT_32->getPixelSize());
        $this->assertEquals(40, FontSize::FONT_40->getPixelSize());
        $this->assertEquals(48, FontSize::FONT_48->getPixelSize());
        $this->assertEquals(56, FontSize::FONT_56->getPixelSize());
    }

    public function testColorEnum()
    {
        $this->assertEquals(0x00, Color::BLACK->value);
        $this->assertEquals(0x01, Color::RED->value);
        $this->assertEquals(0x02, Color::GREEN->value);
        $this->assertEquals(0x03, Color::YELLOW->value);
        $this->assertEquals(0x04, Color::BLUE->value);
        $this->assertEquals(0x05, Color::MAGENTA->value);
        $this->assertEquals(0x06, Color::CYAN->value);
        $this->assertEquals(0x07, Color::WHITE->value);
    }

    public function testColorRgbConstants()
    {
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0], Color::RGB_BLACK);
        $this->assertEquals(['r' => 255, 'g' => 255, 'b' => 255], Color::RGB_WHITE);
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], Color::RGB_RED);
        $this->assertEquals(['r' => 0, 'g' => 128, 'b' => 0], Color::RGB_GREEN);
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255], Color::RGB_BLUE);
    }

    public function testColorToRgb()
    {
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], Color::RED->toRgb());
        $this->assertEquals(['r' => 0, 'g' => 255, 'b' => 0], Color::GREEN->toRgb());
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255], Color::BLUE->toRgb());
        $this->assertEquals(['r' => 255, 'g' => 255, 'b' => 255], Color::WHITE->toRgb());
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0], Color::BLACK->toRgb());
    }

    public function testColorToHex()
    {
        $this->assertEquals('#ff0000', Color::RED->toHex());
        $this->assertEquals('#00ff00', Color::GREEN->toHex());
        $this->assertEquals('#0000ff', Color::BLUE->toHex());
        $this->assertEquals('#ffffff', Color::WHITE->toHex());
        $this->assertEquals('#000000', Color::BLACK->toHex());
    }

    public function testColorHexToRgb()
    {
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('#FF0000'));
        $this->assertEquals(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('#00FF00'));
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255], Color::hexToRgb('#0000FF'));
        $this->assertEquals(['r' => 255, 'g' => 255, 'b' => 255], Color::hexToRgb('#FFFFFF'));
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0], Color::hexToRgb('#000000'));
    }

    public function testColorHexToRgbWithoutHashPrefix()
    {
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('FF0000'));
        $this->assertEquals(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('00FF00'));
    }

    public function testColorHexToRgbShortFormat()
    {
        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('#F00'));
        $this->assertEquals(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('#0F0'));
        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 255], Color::hexToRgb('#00F'));
    }

    public function testColorConvert()
    {
        // Test RGB array conversion
        $result = Color::convert(['r' => 255, 'g' => 128, 'b' => 64]);
        $this->assertEquals(['r' => 255, 'g' => 128, 'b' => 64], $result);

        // Test hex string conversion
        $result = Color::convert('#FF0000');
        if ($result instanceof Color) {
            $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], $result->toRgb());
        } else {
            $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], $result);
        }

        // Test Color enum conversion
        $result = Color::convert(Color::RED);
        $this->assertInstanceOf(Color::class, $result);
        $this->assertEquals(Color::RED, $result);
    }

    public function testColorLighten()
    {
        $lightened = Color::RED->lighten(0.5);
        $this->assertIsArray($lightened);
        $this->assertGreaterThan(255, $lightened['r'] + $lightened['g'] + $lightened['b']);
    }

    public function testColorDarken()
    {
        $darkened = Color::RED->darken(0.5);
        $this->assertIsArray($darkened);
        $this->assertLessThan(255, $darkened['r']);
    }

    public function testColorGetContrasting()
    {
        $this->assertEquals(Color::WHITE, Color::BLACK->getContrasting());
        $this->assertEquals(Color::BLACK, Color::WHITE->getContrasting());
    }

    public function testColorBlendWith()
    {
        $color1 = ['r' => 255, 'g' => 0, 'b' => 0]; // Red
        $color2 = ['r' => 0, 'g' => 0, 'b' => 255]; // Blue
        $blended = Color::RED->blendWith($color2, 0.5);

        $this->assertIsArray($blended);
        $this->assertGreaterThan(0, $blended['r']);
        $this->assertGreaterThan(0, $blended['b']);
    }

    public function testColorBrightness()
    {
        $this->assertEquals(0, Color::BLACK->getBrightness());
        $this->assertEquals(255, Color::WHITE->getBrightness());

        $redBrightness = Color::RED->getBrightness();
        $this->assertGreaterThan(50, $redBrightness);
        $this->assertLessThan(150, $redBrightness);
    }

    public function testColorIsDarkAndLight()
    {
        $this->assertTrue(Color::BLACK->isDark());
        $this->assertFalse(Color::BLACK->isLight());

        $this->assertFalse(Color::WHITE->isDark());
        $this->assertTrue(Color::WHITE->isLight());
    }

    public function testColorCreateGradient()
    {
        $startColor = ['r' => 255, 'g' => 0, 'b' => 0];
        $endColor = ['r' => 0, 'g' => 0, 'b' => 255];
        $gradient = Color::createGradient($startColor, $endColor, 5);

        $this->assertCount(5, $gradient);
        $this->assertEquals($startColor, $gradient[0]);
        $this->assertEquals($endColor, $gradient[4]);
    }

    public function testColorGetAllRgbColors()
    {
        $colors = Color::getAllRgbColors();

        $this->assertIsArray($colors);
        $this->assertArrayHasKey('BLACK', $colors);
        $this->assertArrayHasKey('WHITE', $colors);
        $this->assertArrayHasKey('RED', $colors);
        $this->assertArrayHasKey('GREEN', $colors);
        $this->assertArrayHasKey('BLUE', $colors);
    }

    public function testColorGetAllPalettes()
    {
        $palettes = Color::getAllPalettes();

        $this->assertIsArray($palettes);
        $this->assertArrayHasKey('basic', $palettes);
        $this->assertArrayHasKey('warm', $palettes);
        $this->assertArrayHasKey('cool', $palettes);
        $this->assertArrayHasKey('grayscale', $palettes);
        $this->assertArrayHasKey('status', $palettes);
    }

    public function testEffectEnum()
    {
        $this->assertEquals(0, Effect::DRAW->value);
        $this->assertEquals(11, Effect::SCROLL_LEFT->value);
        $this->assertEquals(12, Effect::SCROLL_RIGHT->value);
        $this->assertEquals(10, Effect::SCROLL_UP->value);
        $this->assertEquals(64, Effect::SCROLL_DOWN->value);
        $this->assertEquals(13, Effect::FLICKER->value);
    }

    public function testAlignmentEnum()
    {
        $this->assertEquals(0, Alignment::LEFT->value);
        $this->assertEquals(1, Alignment::CENTER->value);
        $this->assertEquals(2, Alignment::RIGHT->value);
    }

    public function testVerticalAlignmentEnum()
    {
        $this->assertEquals(0, VerticalAlignment::TOP->value);
        $this->assertEquals(1, VerticalAlignment::CENTER->value);
        $this->assertEquals(2, VerticalAlignment::BOTTOM->value);
    }

    public function testImageModeEnum()
    {
        $this->assertEquals(0, ImageMode::CENTER->value);
        $this->assertEquals(2, ImageMode::STRETCH->value);
        $this->assertEquals(3, ImageMode::TILE->value);
    }

    public function testProtocolConstants()
    {
        $this->assertEquals(0, Protocol::CLOCK_12_HOUR);
        $this->assertEquals(1, Protocol::CLOCK_24_HOUR);
        $this->assertEquals(0, Protocol::CLOCK_YEAR_4_DIGIT);
        $this->assertEquals(1, Protocol::CLOCK_YEAR_2_DIGIT);
        $this->assertEquals(0, Protocol::CLOCK_SINGLE_ROW);
        $this->assertEquals(1, Protocol::CLOCK_MULTI_ROW);
    }
}
