<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Window type enumeration.
 */
enum WindowType: int
{
    case BLANK = 0;
    case TEXT = 1;
    case CLOCK = 2;
    case TEMPERATURE = 3;
    case PICTURE = 4;

    /**
     * Get window type name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::BLANK => 'Blank',
            self::TEXT => 'Text',
            self::CLOCK => 'Clock',
            self::TEMPERATURE => 'Temperature',
            self::PICTURE => 'Picture',
        };
    }

    /**
     * Get window type description.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::BLANK => 'Empty window',
            self::TEXT => 'Text display window',
            self::CLOCK => 'Clock display window',
            self::TEMPERATURE => 'Temperature display window',
            self::PICTURE => 'Picture display window',
        };
    }

    /**
     * Check if window type supports text content.
     */
    public function supportsText(): bool
    {
        return match ($this) {
            self::TEXT, self::CLOCK, self::TEMPERATURE => true,
            default => false,
        };
    }

    /**
     * Check if window type supports image content.
     */
    public function supportsImage(): bool
    {
        return $this === self::PICTURE;
    }
}
