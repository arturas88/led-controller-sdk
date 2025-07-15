<?php

namespace LEDController\Enum;

/**
 * Text processor mode enumeration
 */
enum TextProcessorMode: string
{
    case TEXT = 'text';
    case TRANSLITERATE = 'transliterate';
    case TO_IMAGE = 'to_image';

    /**
     * Get mode description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TEXT => 'Send text as-is',
            self::TRANSLITERATE => 'Convert non-ASCII characters to ASCII equivalents',
            self::TO_IMAGE => 'Render text to image',
        };
    }

    /**
     * Get default mode
     */
    public static function default(): self
    {
        return self::TEXT;
    }

    /**
     * Check if mode produces image output
     */
    public function isImageMode(): bool
    {
        return $this === self::TO_IMAGE;
    }

    /**
     * Check if mode produces text output
     */
    public function isTextMode(): bool
    {
        return $this !== self::TO_IMAGE;
    }

    /**
     * Get all available modes
     */
    public static function getAllModes(): array
    {
        return [
            self::TEXT,
            self::TRANSLITERATE,
            self::TO_IMAGE,
        ];
    }
}
