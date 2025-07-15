<?php

namespace LEDController\Enum;

/**
 * Enhanced Color enumeration with comprehensive color management
 *
 * Provides type-safe color handling with conversion, manipulation, and palette management
 * for LED displays. Supports various color formats and advanced color operations.
 */
enum Color: int
{
    case BLACK = 0x00;
    case RED = 0x01;
    case GREEN = 0x02;
    case YELLOW = 0x03;
    case BLUE = 0x04;
    case MAGENTA = 0x05;
    case CYAN = 0x06;
    case WHITE = 0x07;

    // =========================================================================
    // RGB COLOR CONSTANTS
    // =========================================================================

    // Essential RGB colors for full color mode (24-bit)
    public const RGB_BLACK = ['r' => 0, 'g' => 0, 'b' => 0];
    public const RGB_WHITE = ['r' => 255, 'g' => 255, 'b' => 255];
    public const RGB_RED = ['r' => 255, 'g' => 0, 'b' => 0];
    public const RGB_GREEN = ['r' => 0, 'g' => 128, 'b' => 0];
    public const RGB_BLUE = ['r' => 0, 'g' => 0, 'b' => 255];
    public const RGB_YELLOW = ['r' => 255, 'g' => 255, 'b' => 0];
    public const RGB_MAGENTA = ['r' => 255, 'g' => 0, 'b' => 255];
    public const RGB_CYAN = ['r' => 0, 'g' => 255, 'b' => 255];

    // Common color variants
    public const RGB_DARK_RED = ['r' => 139, 'g' => 0, 'b' => 0];
    public const RGB_DARK_GREEN = ['r' => 0, 'g' => 100, 'b' => 0];
    public const RGB_DARK_BLUE = ['r' => 0, 'g' => 0, 'b' => 139];
    public const RGB_LIGHT_RED = ['r' => 255, 'g' => 192, 'b' => 203];
    public const RGB_LIGHT_GREEN = ['r' => 144, 'g' => 238, 'b' => 144];
    public const RGB_LIGHT_BLUE = ['r' => 173, 'g' => 216, 'b' => 230];

    // Useful grays
    public const RGB_GRAY = ['r' => 128, 'g' => 128, 'b' => 128];
    public const RGB_DARK_GRAY = ['r' => 64, 'g' => 64, 'b' => 64];
    public const RGB_LIGHT_GRAY = ['r' => 192, 'g' => 192, 'b' => 192];

    // Common colors for LED displays
    public const RGB_ORANGE = ['r' => 255, 'g' => 165, 'b' => 0];
    public const RGB_PURPLE = ['r' => 128, 'g' => 0, 'b' => 128];
    public const RGB_PINK = ['r' => 255, 'g' => 192, 'b' => 203];
    public const RGB_BROWN = ['r' => 165, 'g' => 42, 'b' => 42];
    public const RGB_LIME = ['r' => 0, 'g' => 255, 'b' => 0];
    public const RGB_NAVY = ['r' => 0, 'g' => 0, 'b' => 128];
    public const RGB_MAROON = ['r' => 128, 'g' => 0, 'b' => 0];
    public const RGB_TEAL = ['r' => 0, 'g' => 128, 'b' => 128];
    public const RGB_SILVER = ['r' => 192, 'g' => 192, 'b' => 192];
    public const RGB_GOLD = ['r' => 255, 'g' => 215, 'b' => 0];

    // Status colors for LED displays
    public const RGB_SUCCESS = ['r' => 0, 'g' => 255, 'b' => 0];
    public const RGB_WARNING = ['r' => 255, 'g' => 255, 'b' => 0];
    public const RGB_ERROR = ['r' => 255, 'g' => 0, 'b' => 0];
    public const RGB_INFO = ['r' => 0, 'g' => 191, 'b' => 255];

    // Color palettes
    public const PALETTE_BASIC = [
        self::RGB_RED, self::RGB_GREEN, self::RGB_BLUE, self::RGB_YELLOW,
        self::RGB_MAGENTA, self::RGB_CYAN, self::RGB_WHITE, self::RGB_BLACK
    ];

    public const PALETTE_WARM = [
        self::RGB_RED, self::RGB_ORANGE, self::RGB_YELLOW, self::RGB_PINK, self::RGB_GOLD
    ];

    public const PALETTE_COOL = [
        self::RGB_BLUE, self::RGB_CYAN, self::RGB_TEAL, self::RGB_LIME, self::RGB_NAVY
    ];

    public const PALETTE_GRAYSCALE = [
        self::RGB_BLACK, self::RGB_DARK_GRAY, self::RGB_GRAY, self::RGB_LIGHT_GRAY, self::RGB_WHITE
    ];

    public const PALETTE_STATUS = [
        self::RGB_SUCCESS, self::RGB_WARNING, self::RGB_ERROR, self::RGB_INFO
    ];

    // =========================================================================
    // STATIC FACTORY METHODS
    // =========================================================================

    /**
     * Universal color converter - accepts hex strings, RGB arrays, Color enums, or color constants
     * Returns Color enum instance or creates RGB array for complex colors
     */
    public static function convert(string|array|Color|int $color): Color|array
    {
        // Handle Color enum
        if ($color instanceof Color) {
            return $color;
        }

        // Handle hex strings (like "#112233" or "112233")
        if (is_string($color) && preg_match('/^#?[0-9a-fA-F]{3,6}$/', $color)) {
            return self::fromHexString($color);
        }

        // Handle RGB arrays - return as array since we can't create custom enum values
        if (is_array($color)) {
            return [
                'r' => max(0, min(255, (int)($color['r'] ?? 0))),
                'g' => max(0, min(255, (int)($color['g'] ?? 0))),
                'b' => max(0, min(255, (int)($color['b'] ?? 0)))
            ];
        }

        // Handle integer constants
        if (is_int($color)) {
            return match ($color) {
                0x00 => self::BLACK,
                0x01 => self::RED,
                0x02 => self::GREEN,
                0x03 => self::YELLOW,
                0x04 => self::BLUE,
                0x05 => self::MAGENTA,
                0x06 => self::CYAN,
                0x07 => self::WHITE,
                default => self::RED, // Default fallback
            };
        }

        throw new \InvalidArgumentException(
            'Invalid color format. Use hex string, RGB array, Color enum, or color constant.'
        );
    }

    /**
     * Create Color from hex string
     */
    public static function fromHex(string $hex): Color|array
    {
        return self::fromHexString($hex);
    }

    /**
     * Create Color from RGB values
     */
    public static function fromRgb(int $r, int $g, int $b): Color|array
    {
        // Try to match to existing enum first
        $existing = self::fromRgbMatch($r, $g, $b);
        if ($existing !== null) {
            return $existing;
        }

        // Return as RGB array for custom colors
        return [
            'r' => max(0, min(255, $r)),
            'g' => max(0, min(255, $g)),
            'b' => max(0, min(255, $b))
        ];
    }

    /**
     * Create Color from RGB array
     */
    public static function fromArray(array $rgb): Color|array
    {
        return self::fromRgb($rgb['r'] ?? 0, $rgb['g'] ?? 0, $rgb['b'] ?? 0);
    }

    /**
     * Create Color from HSL values
     */
    public static function fromHsl(float $h, float $s, float $l): array
    {
        $h = fmod($h, 360) / 360;
        $s = max(0, min(1, $s));
        $l = max(0, min(1, $l));

        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $hue2rgb = function ($p, $q, $t) {
                if ($t < 0) {
                    $t += 1;
                }

                if ($t > 1) {
                    $t -= 1;
                }

                if ($t < 1 / 6) {
                    return $p + ($q - $p) * 6 * $t;
                }

                if ($t < 1 / 2) {
                    return $q;
                }

                if ($t < 2 / 3) {
                    return $p + ($q - $p) * (2 / 3 - $t) * 6;
                }

                return $p;
            };

            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $hue2rgb($p, $q, $h + 1 / 3);
            $g = $hue2rgb($p, $q, $h);
            $b = $hue2rgb($p, $q, $h - 1 / 3);
        }

        return [
            'r' => (int) round($r * 255),
            'g' => (int) round($g * 255),
            'b' => (int) round($b * 255)
        ];
    }

    // =========================================================================
    // INSTANCE METHODS
    // =========================================================================

    /**
     * Get RGB color array
     */
    public function toRgb(): array
    {
        return match ($this) {
            self::BLACK => ['r' => 0, 'g' => 0, 'b' => 0],
            self::RED => ['r' => 255, 'g' => 0, 'b' => 0],
            self::GREEN => ['r' => 0, 'g' => 255, 'b' => 0],
            self::YELLOW => ['r' => 255, 'g' => 255, 'b' => 0],
            self::BLUE => ['r' => 0, 'g' => 0, 'b' => 255],
            self::MAGENTA => ['r' => 255, 'g' => 0, 'b' => 255],
            self::CYAN => ['r' => 0, 'g' => 255, 'b' => 255],
            self::WHITE => ['r' => 255, 'g' => 255, 'b' => 255],
        };
    }

    /**
     * Get hex color string
     */
    public function toHex(): string
    {
        $rgb = $this->toRgb();
        return sprintf('#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Get color name
     */
    public function getName(): string
    {
        return match ($this) {
            self::BLACK => 'Black',
            self::RED => 'Red',
            self::GREEN => 'Green',
            self::YELLOW => 'Yellow',
            self::BLUE => 'Blue',
            self::MAGENTA => 'Magenta',
            self::CYAN => 'Cyan',
            self::WHITE => 'White',
        };
    }

    /**
     * Lighten the color by percentage
     */
    public function lighten(float $percentage): array
    {
        $rgb = $this->toRgb();
        $percentage = max(0, min(1, $percentage));

        return [
            'r' => min(255, (int) round($rgb['r'] + (255 - $rgb['r']) * $percentage)),
            'g' => min(255, (int) round($rgb['g'] + (255 - $rgb['g']) * $percentage)),
            'b' => min(255, (int) round($rgb['b'] + (255 - $rgb['b']) * $percentage))
        ];
    }

    /**
     * Darken the color by percentage
     */
    public function darken(float $percentage): array
    {
        $rgb = $this->toRgb();
        $percentage = max(0, min(1, $percentage));

        return [
            'r' => max(0, (int) round($rgb['r'] * (1 - $percentage))),
            'g' => max(0, (int) round($rgb['g'] * (1 - $percentage))),
            'b' => max(0, (int) round($rgb['b'] * (1 - $percentage)))
        ];
    }

    /**
     * Blend with another color
     */
    public function blendWith(Color|array $other, float $ratio = 0.5): array
    {
        $rgb1 = $this->toRgb();
        $rgb2 = $other instanceof Color ? $other->toRgb() : $other;

        $ratio = max(0, min(1, $ratio));

        return [
            'r' => (int) round($rgb1['r'] * (1 - $ratio) + $rgb2['r'] * $ratio),
            'g' => (int) round($rgb1['g'] * (1 - $ratio) + $rgb2['g'] * $ratio),
            'b' => (int) round($rgb1['b'] * (1 - $ratio) + $rgb2['b'] * $ratio)
        ];
    }

    /**
     * Get contrasting color (black or white) for readability
     */
    public function getContrasting(): Color
    {
        $rgb = $this->toRgb();
        $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
        return $brightness > 128 ? self::BLACK : self::WHITE;
    }

    /**
     * Get color brightness (0-255)
     */
    public function getBrightness(): int
    {
        $rgb = $this->toRgb();
        return (int) round(($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000);
    }

    /**
     * Check if color is dark
     */
    public function isDark(): bool
    {
        return $this->getBrightness() < 128;
    }

    /**
     * Check if color is light
     */
    public function isLight(): bool
    {
        return $this->getBrightness() >= 128;
    }

    // =========================================================================
    // STATIC UTILITY METHODS
    // =========================================================================

    /**
     * Get RGB color array from R, G, B values
     */
    public static function getRgbColor(int $r, int $g, int $b): array
    {
        return [
            'r' => max(0, min(255, $r)),
            'g' => max(0, min(255, $g)),
            'b' => max(0, min(255, $b))
        ];
    }

    /**
     * Get hex color string from R, G, B values
     */
    public static function getHexColor(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Get RGB color from hex string
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            throw new \InvalidArgumentException('Invalid hex color format');
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Lighten a color by percentage
     */
    public static function lightenColor(array $color, float $percentage): array
    {
        $percentage = max(0, min(1, $percentage));
        return [
            'r' => min(255, (int) round($color['r'] + (255 - $color['r']) * $percentage)),
            'g' => min(255, (int) round($color['g'] + (255 - $color['g']) * $percentage)),
            'b' => min(255, (int) round($color['b'] + (255 - $color['b']) * $percentage))
        ];
    }

    /**
     * Darken a color by percentage
     */
    public static function darkenColor(array $color, float $percentage): array
    {
        $percentage = max(0, min(1, $percentage));
        return [
            'r' => max(0, (int) round($color['r'] * (1 - $percentage))),
            'g' => max(0, (int) round($color['g'] * (1 - $percentage))),
            'b' => max(0, (int) round($color['b'] * (1 - $percentage)))
        ];
    }

    /**
     * Blend two colors together
     */
    public static function blendColors(array $color1, array $color2, float $ratio = 0.5): array
    {
        $ratio = max(0, min(1, $ratio));

        return [
            'r' => (int) round($color1['r'] * (1 - $ratio) + $color2['r'] * $ratio),
            'g' => (int) round($color1['g'] * (1 - $ratio) + $color2['g'] * $ratio),
            'b' => (int) round($color1['b'] * (1 - $ratio) + $color2['b'] * $ratio)
        ];
    }

    /**
     * Get contrasting color (black or white) for readability
     */
    public static function getContrastingColor(array $color): array
    {
        $brightness = ($color['r'] * 299 + $color['g'] * 587 + $color['b'] * 114) / 1000;
        return $brightness > 128 ? self::RGB_BLACK : self::RGB_WHITE;
    }

    /**
     * Get color brightness
     */
    public static function getColorBrightness(array $color): int
    {
        return (int) round(($color['r'] * 299 + $color['g'] * 587 + $color['b'] * 114) / 1000);
    }

    /**
     * Check if color is dark
     */
    public static function isColorDark(array $color): bool
    {
        return self::getColorBrightness($color) < 128;
    }

    /**
     * Check if color is light
     */
    public static function isColorLight(array $color): bool
    {
        return self::getColorBrightness($color) >= 128;
    }

    // =========================================================================
    // PALETTE METHODS
    // =========================================================================

    /**
     * Get color palette by name
     */
    public static function getPalette(string $paletteName): array
    {
        return match (strtolower($paletteName)) {
            'basic' => self::PALETTE_BASIC,
            'warm' => self::PALETTE_WARM,
            'cool' => self::PALETTE_COOL,
            'grayscale' => self::PALETTE_GRAYSCALE,
            'status' => self::PALETTE_STATUS,
            default => self::PALETTE_BASIC,
        };
    }

    /**
     * Get random color from palette
     */
    public static function getRandomPaletteColor(array $palette): array
    {
        return $palette[array_rand($palette)];
    }

    /**
     * Create gradient between two colors
     */
    public static function createGradient(array $startColor, array $endColor, int $steps): array
    {
        if ($steps < 2) {
            throw new \InvalidArgumentException('Gradient must have at least 2 steps');
        }

        $gradient = [];
        for ($i = 0; $i < $steps; $i++) {
            $ratio = $i / ($steps - 1);
            $gradient[] = [
                'r' => (int) round($startColor['r'] * (1 - $ratio) + $endColor['r'] * $ratio),
                'g' => (int) round($startColor['g'] * (1 - $ratio) + $endColor['g'] * $ratio),
                'b' => (int) round($startColor['b'] * (1 - $ratio) + $endColor['b'] * $ratio)
            ];
        }

        return $gradient;
    }

    /**
     * Get color name from RGB values
     */
    public static function getColorName(array $color): string
    {
        // Try to match to known colors
        $distances = [];
        $knownColors = [
            'Black' => self::RGB_BLACK,
            'White' => self::RGB_WHITE,
            'Red' => self::RGB_RED,
            'Green' => self::RGB_GREEN,
            'Blue' => self::RGB_BLUE,
            'Yellow' => self::RGB_YELLOW,
            'Magenta' => self::RGB_MAGENTA,
            'Cyan' => self::RGB_CYAN,
            'Orange' => self::RGB_ORANGE,
            'Purple' => self::RGB_PURPLE,
            'Pink' => self::RGB_PINK,
            'Brown' => self::RGB_BROWN,
            'Lime' => self::RGB_LIME,
            'Navy' => self::RGB_NAVY,
            'Maroon' => self::RGB_MAROON,
            'Teal' => self::RGB_TEAL,
            'Silver' => self::RGB_SILVER,
            'Gold' => self::RGB_GOLD,
            'Gray' => self::RGB_GRAY,
        ];

        foreach ($knownColors as $name => $rgb) {
            $distance = sqrt(
                pow($color['r'] - $rgb['r'], 2) +
                pow($color['g'] - $rgb['g'], 2) +
                pow($color['b'] - $rgb['b'], 2)
            );
            $distances[$name] = $distance;
        }

        asort($distances);
        $closest = array_key_first($distances);

        // If very close, return the name, otherwise return RGB notation
        return $distances[$closest] < 50 ? $closest : sprintf('RGB(%d,%d,%d)', $color['r'], $color['g'], $color['b']);
    }

    /**
     * Get all RGB colors
     */
    public static function getAllRgbColors(): array
    {
        return [
            'BLACK' => self::RGB_BLACK,
            'WHITE' => self::RGB_WHITE,
            'RED' => self::RGB_RED,
            'GREEN' => self::RGB_GREEN,
            'BLUE' => self::RGB_BLUE,
            'YELLOW' => self::RGB_YELLOW,
            'MAGENTA' => self::RGB_MAGENTA,
            'CYAN' => self::RGB_CYAN,
            'ORANGE' => self::RGB_ORANGE,
            'PURPLE' => self::RGB_PURPLE,
            'PINK' => self::RGB_PINK,
            'BROWN' => self::RGB_BROWN,
            'LIME' => self::RGB_LIME,
            'NAVY' => self::RGB_NAVY,
            'MAROON' => self::RGB_MAROON,
            'TEAL' => self::RGB_TEAL,
            'SILVER' => self::RGB_SILVER,
            'GOLD' => self::RGB_GOLD,
            'GRAY' => self::RGB_GRAY,
            'DARK_GRAY' => self::RGB_DARK_GRAY,
            'LIGHT_GRAY' => self::RGB_LIGHT_GRAY,
            'SUCCESS' => self::RGB_SUCCESS,
            'WARNING' => self::RGB_WARNING,
            'ERROR' => self::RGB_ERROR,
            'INFO' => self::RGB_INFO,
        ];
    }

    /**
     * Get all palettes
     */
    public static function getAllPalettes(): array
    {
        return [
            'basic' => self::PALETTE_BASIC,
            'warm' => self::PALETTE_WARM,
            'cool' => self::PALETTE_COOL,
            'grayscale' => self::PALETTE_GRAYSCALE,
            'status' => self::PALETTE_STATUS,
        ];
    }

    /**
     * Get all available colors
     */
    public static function getAllColors(): array
    {
        return [
            self::BLACK,
            self::RED,
            self::GREEN,
            self::YELLOW,
            self::BLUE,
            self::MAGENTA,
            self::CYAN,
            self::WHITE,
        ];
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Helper method to convert hex string to Color or RGB array
     */
    private static function fromHexString(string $hex): Color|array
    {
        $rgb = self::hexToRgb($hex);

        // Try to match to existing enum first
        $existing = self::fromRgbMatch($rgb['r'], $rgb['g'], $rgb['b']);
        if ($existing !== null) {
            return $existing;
        }

        // Return as RGB array for custom colors
        return $rgb;
    }

    /**
     * Helper method to match RGB values to existing Color enum
     */
    private static function fromRgbMatch(int $r, int $g, int $b): ?Color
    {
        // Exact matches for basic colors
        if ($r == 0 && $g == 0 && $b == 0) {
            return self::BLACK;
        }
        if ($r == 255 && $g == 255 && $b == 255) {
            return self::WHITE;
        }
        if ($r == 255 && $g == 0 && $b == 0) {
            return self::RED;
        }
        if ($r == 0 && $g == 255 && $b == 0) {
            return self::GREEN;
        }
        if ($r == 0 && $g == 0 && $b == 255) {
            return self::BLUE;
        }
        if ($r == 255 && $g == 255 && $b == 0) {
            return self::YELLOW;
        }
        if ($r == 255 && $g == 0 && $b == 255) {
            return self::MAGENTA;
        }
        if ($r == 0 && $g == 255 && $b == 255) {
            return self::CYAN;
        }

        // Approximate matches for common variations
        if ($r > 200 && $g < 100 && $b < 100) {
            return self::RED;
        }
        if ($r < 100 && $g > 200 && $b < 100) {
            return self::GREEN;
        }
        if ($r < 100 && $g < 100 && $b > 200) {
            return self::BLUE;
        }
        if ($r > 200 && $g > 200 && $b < 100) {
            return self::YELLOW;
        }
        if ($r > 200 && $g < 100 && $b > 200) {
            return self::MAGENTA;
        }
        if ($r < 100 && $g > 200 && $b > 200) {
            return self::CYAN;
        }

        return null;
    }
}
