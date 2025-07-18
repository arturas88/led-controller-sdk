<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Image mode enumeration.
 */
enum ImageMode: int
{
    case CENTER = 0;
    case ZOOM = 1;
    case STRETCH = 2;
    case TILE = 3;

    /**
     * Get image mode name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::CENTER => 'Center',
            self::ZOOM => 'Zoom',
            self::STRETCH => 'Stretch',
            self::TILE => 'Tile',
        };
    }

    /**
     * Get image mode description.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CENTER => 'Center image in window',
            self::ZOOM => 'Zoom image to fit window',
            self::STRETCH => 'Stretch image to fill window',
            self::TILE => 'Tile image across window',
        };
    }

    /**
     * Check if mode maintains aspect ratio.
     */
    public function maintainsAspectRatio(): bool
    {
        return match ($this) {
            self::CENTER, self::ZOOM, self::TILE => true,
            self::STRETCH => false,
        };
    }
}
