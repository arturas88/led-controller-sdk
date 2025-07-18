<?php

declare(strict_types=1);

namespace LEDController\Tests\Unit;

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;
use LEDController\Enum\VerticalAlignment;
use PHPUnit\Framework\TestCase;

class LEDControllerTest extends TestCase
{
    public function testFontSizeEnum()
    {
        self::assertSame(8, FontSize::FONT_8->getPixelSize());
        self::assertSame(12, FontSize::FONT_12->getPixelSize());
        self::assertSame(16, FontSize::FONT_16->getPixelSize());
        self::assertSame(24, FontSize::FONT_24->getPixelSize());
        self::assertSame(32, FontSize::FONT_32->getPixelSize());
        self::assertSame(40, FontSize::FONT_40->getPixelSize());
        self::assertSame(48, FontSize::FONT_48->getPixelSize());
        self::assertSame(56, FontSize::FONT_56->getPixelSize());
    }

    public function testColorEnum()
    {
        self::assertSame(0x00, Color::BLACK->value);
        self::assertSame(0x01, Color::RED->value);
        self::assertSame(0x02, Color::GREEN->value);
        self::assertSame(0x03, Color::YELLOW->value);
        self::assertSame(0x04, Color::BLUE->value);
        self::assertSame(0x05, Color::MAGENTA->value);
        self::assertSame(0x06, Color::CYAN->value);
        self::assertSame(0x07, Color::WHITE->value);
    }

    public function testColorRgbConstants()
    {
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0], Color::RGB_BLACK);
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255], Color::RGB_WHITE);
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::RGB_RED);
        self::assertSame(['r' => 0, 'g' => 128, 'b' => 0], Color::RGB_GREEN);
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::RGB_BLUE);
    }

    public function testColorToRgb()
    {
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::RED->toRgb());
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::GREEN->toRgb());
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::BLUE->toRgb());
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255], Color::WHITE->toRgb());
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0], Color::BLACK->toRgb());
    }

    public function testColorToHex()
    {
        self::assertSame('#ff0000', Color::RED->toHex());
        self::assertSame('#00ff00', Color::GREEN->toHex());
        self::assertSame('#0000ff', Color::BLUE->toHex());
        self::assertSame('#ffffff', Color::WHITE->toHex());
        self::assertSame('#000000', Color::BLACK->toHex());
    }

    public function testColorHexToRgb()
    {
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('#FF0000'));
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('#00FF00'));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::hexToRgb('#0000FF'));
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255], Color::hexToRgb('#FFFFFF'));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0], Color::hexToRgb('#000000'));
    }

    public function testColorHexToRgbWithoutHashPrefix()
    {
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('FF0000'));
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('00FF00'));
    }

    public function testColorHexToRgbShortFormat()
    {
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::hexToRgb('#F00'));
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::hexToRgb('#0F0'));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::hexToRgb('#00F'));
    }

    public function testColorConvert()
    {
        // Test RGB array conversion
        $result = Color::convert(['r' => 255, 'g' => 128, 'b' => 64]);
        self::assertSame(['r' => 255, 'g' => 128, 'b' => 64], $result);

        // Test hex string conversion
        $result = Color::convert('#FF0000');
        if ($result instanceof Color) {
            self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], $result->toRgb());
        } else {
            self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], $result);
        }

        // Test Color enum conversion
        $result = Color::convert(Color::RED);
        self::assertInstanceOf(Color::class, $result);
        self::assertSame(Color::RED, $result);
    }

    public function testColorLighten()
    {
        $lightened = Color::RED->lighten(0.5);
        self::assertIsArray($lightened);
        self::assertGreaterThan(255, $lightened['r'] + $lightened['g'] + $lightened['b']);
    }

    public function testColorDarken()
    {
        $darkened = Color::RED->darken(0.5);
        self::assertIsArray($darkened);
        self::assertLessThan(255, $darkened['r']);
    }

    public function testColorGetContrasting()
    {
        self::assertSame(Color::WHITE, Color::BLACK->getContrasting());
        self::assertSame(Color::BLACK, Color::WHITE->getContrasting());
    }

    public function testColorBlendWith()
    {
        $color1 = ['r' => 255, 'g' => 0, 'b' => 0]; // Red
        $color2 = ['r' => 0, 'g' => 0, 'b' => 255]; // Blue
        $blended = Color::RED->blendWith($color2, 0.5);

        self::assertIsArray($blended);
        self::assertGreaterThan(0, $blended['r']);
        self::assertGreaterThan(0, $blended['b']);
    }

    public function testColorBrightness()
    {
        self::assertSame(0, Color::BLACK->getBrightness());
        self::assertSame(255, Color::WHITE->getBrightness());

        $redBrightness = Color::RED->getBrightness();
        self::assertGreaterThan(50, $redBrightness);
        self::assertLessThan(150, $redBrightness);
    }

    public function testColorIsDarkAndLight()
    {
        self::assertTrue(Color::BLACK->isDark());
        self::assertFalse(Color::BLACK->isLight());

        self::assertFalse(Color::WHITE->isDark());
        self::assertTrue(Color::WHITE->isLight());
    }

    public function testColorCreateGradient()
    {
        $startColor = ['r' => 255, 'g' => 0, 'b' => 0];
        $endColor = ['r' => 0, 'g' => 0, 'b' => 255];
        $gradient = Color::createGradient($startColor, $endColor, 5);

        self::assertCount(5, $gradient);
        self::assertSame($startColor, $gradient[0]);
        self::assertSame($endColor, $gradient[4]);
    }

    public function testColorGetAllRgbColors()
    {
        $colors = Color::getAllRgbColors();

        self::assertIsArray($colors);
        self::assertArrayHasKey('BLACK', $colors);
        self::assertArrayHasKey('WHITE', $colors);
        self::assertArrayHasKey('RED', $colors);
        self::assertArrayHasKey('GREEN', $colors);
        self::assertArrayHasKey('BLUE', $colors);
    }

    public function testColorGetAllPalettes()
    {
        $palettes = Color::getAllPalettes();

        self::assertIsArray($palettes);
        self::assertArrayHasKey('basic', $palettes);
        self::assertArrayHasKey('warm', $palettes);
        self::assertArrayHasKey('cool', $palettes);
        self::assertArrayHasKey('grayscale', $palettes);
        self::assertArrayHasKey('status', $palettes);
    }

    public function testEffectEnum()
    {
        self::assertSame(0, Effect::DRAW->value);
        self::assertSame(11, Effect::SCROLL_LEFT->value);
        self::assertSame(12, Effect::SCROLL_RIGHT->value);
        self::assertSame(10, Effect::SCROLL_UP->value);
        self::assertSame(64, Effect::SCROLL_DOWN->value);
        self::assertSame(13, Effect::FLICKER->value);
    }

    public function testAlignmentEnum()
    {
        self::assertSame(0, Alignment::LEFT->value);
        self::assertSame(1, Alignment::CENTER->value);
        self::assertSame(2, Alignment::RIGHT->value);
    }

    public function testVerticalAlignmentEnum()
    {
        self::assertSame(0, VerticalAlignment::TOP->value);
        self::assertSame(1, VerticalAlignment::CENTER->value);
        self::assertSame(2, VerticalAlignment::BOTTOM->value);
    }

    public function testImageModeEnum()
    {
        self::assertSame(0, ImageMode::CENTER->value);
        self::assertSame(2, ImageMode::STRETCH->value);
        self::assertSame(3, ImageMode::TILE->value);
    }

    public function testProtocolConstants()
    {
        self::assertSame(0, Protocol::CLOCK_12_HOUR);
        self::assertSame(1, Protocol::CLOCK_24_HOUR);
        self::assertSame(0, Protocol::CLOCK_YEAR_4_DIGIT);
        self::assertSame(1, Protocol::CLOCK_YEAR_2_DIGIT);
        self::assertSame(0, Protocol::CLOCK_SINGLE_ROW);
        self::assertSame(1, Protocol::CLOCK_MULTI_ROW);
    }
}
