<?php

namespace LEDController\Enum;

/**
 * Vertical alignment enumeration
 */
enum VerticalAlignment: int
{
    case TOP = 0;
    case CENTER = 1;
    case BOTTOM = 2;

    /**
     * Get vertical alignment name
     */
    public function getName(): string
    {
        return match ($this) {
            self::TOP => 'Top',
            self::CENTER => 'Center',
            self::BOTTOM => 'Bottom',
        };
    }

    /**
     * Get CSS equivalent
     */
    public function getCssValue(): string
    {
        return match ($this) {
            self::TOP => 'top',
            self::CENTER => 'middle',
            self::BOTTOM => 'bottom',
        };
    }
}
