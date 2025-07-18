<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Font style enumeration.
 */
enum FontStyle: int
{
    case STYLE_0 = 0; // Default font
    case STYLE_1 = 1;
    case STYLE_2 = 2;
    case STYLE_3 = 3;
    case STYLE_4 = 4;
    case STYLE_5 = 5;
    case STYLE_6 = 6;
    case STYLE_7 = 7;

    /**
     * Get style name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::STYLE_0 => 'Default',
            self::STYLE_1 => 'Style 1',
            self::STYLE_2 => 'Style 2',
            self::STYLE_3 => 'Style 3',
            self::STYLE_4 => 'Style 4',
            self::STYLE_5 => 'Style 5',
            self::STYLE_6 => 'Style 6',
            self::STYLE_7 => 'Style 7',
        };
    }

    /**
     * Get default style.
     */
    public static function default(): self
    {
        return self::STYLE_0;
    }

    /**
     * Get all available styles.
     *
     * @return array<int, self> Array of all available font styles
     */
    public static function getAllStyles(): array
    {
        return [
            self::STYLE_0,
            self::STYLE_1,
            self::STYLE_2,
            self::STYLE_3,
            self::STYLE_4,
            self::STYLE_5,
            self::STYLE_6,
            self::STYLE_7,
        ];
    }
}
