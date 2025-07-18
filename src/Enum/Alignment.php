<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Horizontal alignment enumeration.
 */
enum Alignment: int
{
    case LEFT = 0;
    case CENTER = 1;
    case RIGHT = 2;

    /**
     * Get alignment name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::LEFT => 'Left',
            self::CENTER => 'Center',
            self::RIGHT => 'Right',
        };
    }

    /**
     * Get CSS equivalent.
     */
    public function getCssValue(): string
    {
        return match ($this) {
            self::LEFT => 'left',
            self::CENTER => 'center',
            self::RIGHT => 'right',
        };
    }
}
