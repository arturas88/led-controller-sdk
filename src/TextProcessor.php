<?php

namespace LEDController;

use LEDController\Enum\TextProcessorMode;
use LEDController\Enum\Color;

/**
 * Simplified Text Processor for LED Controllers
 *
 * Processes text with explicit modes only - no automatic guessing or analysis.
 * Now modernized with PHP 8.1+ enums and match expressions.
 */
class TextProcessor
{
    // Legacy constants for backward compatibility
    const MODE_TEXT = 'text';
    const MODE_TRANSLITERATE = 'transliterate';
    const MODE_TO_IMAGE = 'to_image';

    private readonly array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'mode' => TextProcessorMode::TEXT->value,
            'width' => 128,
            'height' => 32,
            'font_size' => 16,
            'font_path' => null,
            'color' => [255, 255, 255],
            'background_color' => [0, 0, 0]
        ], $config);
    }

    /**
     * Process text with specified mode (using modern enum)
     */
    public function processText(string $text, array $options = []): array
    {
        $options = array_merge($this->config, $options);
        $mode = $options['mode'] ?? TextProcessorMode::TEXT->value;

        // Handle enum or string mode
        if (is_string($mode)) {
            $enumMode = TextProcessorMode::tryFrom($mode);
            if ($enumMode === null) {
                throw new \InvalidArgumentException("Unknown processing mode: $mode. Use TextProcessorMode enum values.");
            }
            $mode = $enumMode;
        }

        return match ($mode) {
            TextProcessorMode::TEXT => [
                'type' => 'text',
                'content' => $text,
                'method' => 'text'
            ],
            TextProcessorMode::TRANSLITERATE => [
                'type' => 'text',
                'content' => self::transliterateText($text),
                'method' => 'transliterate',
                'original_text' => $text
            ],
            TextProcessorMode::TO_IMAGE => [
                'type' => 'image',
                'content' => self::renderTextToImage($text, $options),
                'method' => 'to_image',
                'image_dimensions' => ['width' => $options['width'], 'height' => $options['height']]
            ],
            default => [
                'type' => 'text',
                'content' => $text,
                'method' => 'text'
            ],
        };
    }

    /**
     * Process text with enum mode (modern approach)
     */
    public function processTextWithMode(string $text, TextProcessorMode $mode, array $options = []): array
    {
        $options = array_merge($this->config, $options);

        return match ($mode) {
            TextProcessorMode::TEXT => [
                'type' => 'text',
                'content' => $text,
                'method' => 'text'
            ],
            TextProcessorMode::TRANSLITERATE => [
                'type' => 'text',
                'content' => self::transliterateText($text),
                'method' => 'transliterate',
                'original_text' => $text
            ],
            TextProcessorMode::TO_IMAGE => [
                'type' => 'image',
                'content' => self::renderTextToImage($text, $options),
                'method' => 'to_image',
                'image_dimensions' => ['width' => $options['width'], 'height' => $options['height']]
            ],
        };
    }

    /**
     * Transliterate text to ASCII
     */
    public static function transliterateText(string $text, array $options = []): string
    {
        $fromEncoding = $options['from'] ?? 'UTF-8';
        $toEncoding = $options['to'] ?? 'ASCII//TRANSLIT';

        // Method 1: iconv with transliteration
        $result = @iconv($fromEncoding, $toEncoding, $text);

        if ($result === false) {
            // Method 2: Use Transliterator class if available
            if (class_exists('Transliterator')) {
                $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
                if ($transliterator) {
                    $result = $transliterator->transliterate($text);
                }
            }
        }

        // Fallback to original text if transliteration fails
        return $result !== false ? $result : $text;
    }

    /**
     * Render text to image
     */
    public static function renderTextToImage(string $text, array $options = []): string
    {
        $width = $options['width'] ?? 128;
        $height = $options['height'] ?? 32;
        $fontSize = $options['font_size'] ?? 16;
        $fontPath = $options['font_path'] ?? null;

        // Universal color support: hex strings, RGB arrays, Color enums, or color constants
        $color = Color::convert($options['color'] ?? [255, 255, 255]);
        $backgroundColor = Color::convert($options['background_color'] ?? [0, 0, 0]);

        // Try to get font path
        if (!$fontPath) {
            try {
                $fontPath = self::getDefaultFontPath();
            } catch (\Exception $e) {
                // Fallback to built-in fonts if TTF fonts not available
                return self::renderTextToImageBuiltin($text, $options);
            }
        }

        // Create image
        $image = imagecreate($width, $height);

        // Set colors
        $bgColor = imagecolorallocate($image, $backgroundColor['r'], $backgroundColor['g'], $backgroundColor['b']);
        $textColor = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

        // Fill background
        imagefill($image, 0, 0, $bgColor);

        // Calculate text position for centering
        $textBox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = $textBox[4] - $textBox[0];
        $textHeight = $textBox[1] - $textBox[7];

        $x = (int)(($width - $textWidth) / 2);
        $y = (int)(($height - $textHeight) / 2 + $textHeight);

        // Draw text with proper font
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);

        // Convert to LED controller compatible format
        $imageData = self::convertToLEDFormat($image, $width, $height);

        // Clean up
        imagedestroy($image);

        return $imageData;
    }

    /**
     * Get default font path
     */
    private static function getDefaultFontPath(): string
    {
        // Look for system fonts
        $fontPaths = [
            dirname(__DIR__) . '/fonts/NotoSans-Regular.ttf',
            dirname(__DIR__) . '/fonts/RobotoSlab-Regular.ttf',
            '/System/Library/Fonts/Arial.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/TTF/arial.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\calibri.ttf',
        ];

        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('No TTF font found. Using built-in fonts instead.');
    }

    /**
     * Render text to image using built-in fonts
     */
    private static function renderTextToImageBuiltin(string $text, array $options = []): string
    {
        $width = $options['width'] ?? 128;
        $height = $options['height'] ?? 32;

        // Universal color support: hex strings, RGB arrays, Color enums, or color constants
        $color = Color::convert($options['color'] ?? [255, 255, 255]);
        $backgroundColor = Color::convert($options['background_color'] ?? [0, 0, 0]);

        // Create image
        $image = imagecreate($width, $height);

        // Set colors
        $bgColor = imagecolorallocate($image, $backgroundColor['r'], $backgroundColor['g'], $backgroundColor['b']);
        $textColor = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

        // Fill background
        imagefill($image, 0, 0, $bgColor);

        // Use built-in font (font 5 is largest built-in font)
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        $x = (int)(($width - $textWidth) / 2);
        $y = (int)(($height - $textHeight) / 2);

        // Draw text with built-in font
        imagestring($image, $font, $x, $y, $text, $textColor);

        // Convert to LED controller compatible format
        $imageData = self::convertToLEDFormat($image, $width, $height);

        // Clean up
        imagedestroy($image);

        return $imageData;
    }

    /**
     * Convert image to LED controller format
     */
    private static function convertToLEDFormat($image, int $width, int $height): string
    {
        // This is a simplified conversion - in practice, you'd need to convert
        // the image to the specific format expected by the LED controller

        $imageData = '';

        // Convert to raw pixel data
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $rgb = imagecolorsforindex($image, $colorIndex);

                // Convert to LED controller format (this is controller-specific)
                $r = intval($rgb['red'] / 8);   // 5-bit red
                $g = intval($rgb['green'] / 4); // 6-bit green
                $b = intval($rgb['blue'] / 8);  // 5-bit blue

                // Pack into 16-bit RGB565 format
                $pixel = ($r << 11) | ($g << 5) | $b;
                $imageData .= pack('v', $pixel); // Little-endian 16-bit
            }
        }

        return $imageData;
    }

    /**
     * Get default mode
     */
    public static function getDefaultMode(): TextProcessorMode
    {
        return TextProcessorMode::TEXT;
    }

    /**
     * Check if mode is available
     */
    public static function isModeAvailable(TextProcessorMode|string $mode): bool
    {
        if ($mode instanceof TextProcessorMode) {
            return true;
        }

        return TextProcessorMode::tryFrom($mode) !== null;
    }

    /**
     * Get all available modes
     */
    public static function getAvailableModes(): array
    {
        return TextProcessorMode::cases();
    }
}
