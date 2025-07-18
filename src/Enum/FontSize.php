<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Font size enumeration.
 */
enum FontSize: int
{
    case FONT_8 = 0;
    case FONT_12 = 1;
    case FONT_16 = 2;
    case FONT_24 = 3;
    case FONT_32 = 4;
    case FONT_40 = 5;
    case FONT_48 = 6;
    case FONT_56 = 7;

    /**
     * Get font size in pixels.
     */
    public function getPixelSize(): int
    {
        return match ($this) {
            self::FONT_8 => 8,
            self::FONT_12 => 12,
            self::FONT_16 => 16,
            self::FONT_24 => 24,
            self::FONT_32 => 32,
            self::FONT_40 => 40,
            self::FONT_48 => 48,
            self::FONT_56 => 56,
        };
    }

    /**
     * Get font size from pixel size.
     */
    public static function fromPixelSize(int $pixels): self
    {
        return match ($pixels) {
            8 => self::FONT_8,
            12 => self::FONT_12,
            16 => self::FONT_16,
            24 => self::FONT_24,
            32 => self::FONT_32,
            40 => self::FONT_40,
            48 => self::FONT_48,
            56 => self::FONT_56,
            default => self::FONT_16,
        };
    }

    /**
     * Get all available font sizes.
     *
     * @return array<int, int> Array of all available font sizes in pixels
     */
    public static function getAllSizes(): array
    {
        return [
            self::FONT_8->getPixelSize(),
            self::FONT_12->getPixelSize(),
            self::FONT_16->getPixelSize(),
            self::FONT_24->getPixelSize(),
            self::FONT_32->getPixelSize(),
            self::FONT_40->getPixelSize(),
            self::FONT_48->getPixelSize(),
            self::FONT_56->getPixelSize(),
        ];
    }

    /**
     * Get font size by code (for backward compatibility).
     */
    public static function getFontSize(int $fontCode): int
    {
        return match ($fontCode) {
            0 => 8,
            1 => 12,
            2 => 16,
            3 => 24,
            4 => 32,
            5 => 40,
            6 => 48,
            7 => 56,
            default => 16,
        };
    }
}
