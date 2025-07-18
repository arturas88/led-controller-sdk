<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Effect enumeration for display effects.
 */
enum Effect: int
{
    case DRAW = 0;
    case OPEN_LEFT = 1;
    case OPEN_RIGHT = 2;
    case OPEN_CENTER_H = 3;
    case OPEN_CENTER_V = 4;
    case SHUTTER_V = 5;
    case MOVE_LEFT = 6;
    case MOVE_RIGHT = 7;
    case MOVE_UP = 8;
    case MOVE_DOWN = 9;
    case SCROLL_UP = 10;
    case SCROLL_LEFT = 11;
    case SCROLL_RIGHT = 12;
    case FLICKER = 13;
    case CONTINUOUS_SCROLL_LEFT = 14;
    case CONTINUOUS_SCROLL_RIGHT = 15;
    case SHUTTER_H = 16;
    case CLOCKWISE_OPEN = 17;
    case ANTICLOCKWISE_OPEN = 18;
    case WINDMILL = 19;
    case WINDMILL_ANTI = 20;
    case RECTANGLE_FORTH = 21;
    case RECTANGLE_ENTAD = 22;
    case QUADRANGLE_FORTH = 23;
    case QUADRANGLE_ENTAD = 24;
    case CIRCLE_FORTH = 25;
    case CIRCLE_ENTAD = 26;
    case OPEN_LEFT_UP = 27;
    case OPEN_RIGHT_UP = 28;
    case OPEN_LEFT_BOTTOM = 29;
    case OPEN_RIGHT_BOTTOM = 30;
    case BEVEL_OPEN = 31;
    case ANTIBEVEL_OPEN = 32;
    case ENTER_LEFT_UP = 33;
    case ENTER_RIGHT_UP = 34;
    case ENTER_LEFT_BOTTOM = 35;
    case ENTER_RIGHT_BOTTOM = 36;
    case BEVEL_ENTER = 37;
    case ANTIBEVEL_ENTER = 38;
    case ZEBRA_H = 39;
    case ZEBRA_V = 40;
    case MOSAIC_BIG = 41;
    case MOSAIC_SMALL = 42;
    case RADIATION_UP = 43;
    case RADIATION_DOWN = 44;
    case AMASS = 45;
    case DROP = 46;
    case COMBINATION_H = 47;
    case COMBINATION_V = 48;
    case BACKOUT = 49;
    case SCREWING = 50;
    case CHESSBOARD_H = 51;
    case CHESSBOARD_V = 52;
    case CONTINUOUS_SCROLL_UP = 53;
    case CONTINUOUS_SCROLL_DOWN = 54;
    case GRADUAL_BIGGER_UP = 57;
    case GRADUAL_SMALLER_DOWN = 58;
    case GRADUAL_BIGGER_VERTICAL = 60;
    case FLICKER_H = 61;
    case FLICKER_V = 62;
    case SNOW = 63;
    case SCROLL_DOWN = 64;
    case SCROLL_LEFT_TO_RIGHT = 65;
    case OPEN_TOP_TO_BOTTOM = 66;
    case SECTOR_EXPAND = 67;
    case ZEBRA_CROSSING_H = 69;
    case ZEBRA_CROSSING_V = 70;
    case RANDOM = 32768;

    /**
     * Get effect name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::DRAW => 'Draw',
            self::OPEN_LEFT => 'Open Left',
            self::OPEN_RIGHT => 'Open Right',
            self::OPEN_CENTER_H => 'Open Center H',
            self::OPEN_CENTER_V => 'Open Center V',
            self::SHUTTER_V => 'Shutter V',
            self::MOVE_LEFT => 'Move Left',
            self::MOVE_RIGHT => 'Move Right',
            self::MOVE_UP => 'Move Up',
            self::MOVE_DOWN => 'Move Down',
            self::SCROLL_UP => 'Scroll Up',
            self::SCROLL_LEFT => 'Scroll Left',
            self::SCROLL_RIGHT => 'Scroll Right',
            self::FLICKER => 'Flicker',
            self::CONTINUOUS_SCROLL_LEFT => 'Continuous Scroll Left',
            self::CONTINUOUS_SCROLL_RIGHT => 'Continuous Scroll Right',
            self::SHUTTER_H => 'Shutter H',
            self::CLOCKWISE_OPEN => 'Clockwise Open',
            self::ANTICLOCKWISE_OPEN => 'Anticlockwise Open',
            self::WINDMILL => 'Windmill',
            self::WINDMILL_ANTI => 'Windmill Anti',
            self::RECTANGLE_FORTH => 'Rectangle Forth',
            self::RECTANGLE_ENTAD => 'Rectangle Entad',
            self::QUADRANGLE_FORTH => 'Quadrangle Forth',
            self::QUADRANGLE_ENTAD => 'Quadrangle Entad',
            self::CIRCLE_FORTH => 'Circle Forth',
            self::CIRCLE_ENTAD => 'Circle Entad',
            self::OPEN_LEFT_UP => 'Open Left Up',
            self::OPEN_RIGHT_UP => 'Open Right Up',
            self::OPEN_LEFT_BOTTOM => 'Open Left Bottom',
            self::OPEN_RIGHT_BOTTOM => 'Open Right Bottom',
            self::BEVEL_OPEN => 'Bevel Open',
            self::ANTIBEVEL_OPEN => 'Antibevel Open',
            self::ENTER_LEFT_UP => 'Enter Left Up',
            self::ENTER_RIGHT_UP => 'Enter Right Up',
            self::ENTER_LEFT_BOTTOM => 'Enter Left Bottom',
            self::ENTER_RIGHT_BOTTOM => 'Enter Right Bottom',
            self::BEVEL_ENTER => 'Bevel Enter',
            self::ANTIBEVEL_ENTER => 'Antibevel Enter',
            self::ZEBRA_H => 'Zebra H',
            self::ZEBRA_V => 'Zebra V',
            self::MOSAIC_BIG => 'Mosaic Big',
            self::MOSAIC_SMALL => 'Mosaic Small',
            self::RADIATION_UP => 'Radiation Up',
            self::RADIATION_DOWN => 'Radiation Down',
            self::AMASS => 'Amass',
            self::DROP => 'Drop',
            self::COMBINATION_H => 'Combination H',
            self::COMBINATION_V => 'Combination V',
            self::BACKOUT => 'Backout',
            self::SCREWING => 'Screwing',
            self::CHESSBOARD_H => 'Chessboard H',
            self::CHESSBOARD_V => 'Chessboard V',
            self::CONTINUOUS_SCROLL_UP => 'Continuous Scroll Up',
            self::CONTINUOUS_SCROLL_DOWN => 'Continuous Scroll Down',
            self::GRADUAL_BIGGER_UP => 'Gradual Bigger Up',
            self::GRADUAL_SMALLER_DOWN => 'Gradual Smaller Down',
            self::GRADUAL_BIGGER_VERTICAL => 'Gradual Bigger Vertical',
            self::FLICKER_H => 'Flicker H',
            self::FLICKER_V => 'Flicker V',
            self::SNOW => 'Snow',
            self::SCROLL_DOWN => 'Scroll Down',
            self::SCROLL_LEFT_TO_RIGHT => 'Scroll Left to Right',
            self::OPEN_TOP_TO_BOTTOM => 'Open Top to Bottom',
            self::SECTOR_EXPAND => 'Sector Expand',
            self::ZEBRA_CROSSING_H => 'Zebra Crossing H',
            self::ZEBRA_CROSSING_V => 'Zebra Crossing V',
            self::RANDOM => 'Random',
        };
    }

    /**
     * Get effect category.
     */
    public function getCategory(): string
    {
        return match ($this) {
            self::DRAW => 'Static',
            self::OPEN_LEFT, self::OPEN_RIGHT, self::OPEN_CENTER_H, self::OPEN_CENTER_V,
            self::OPEN_LEFT_UP, self::OPEN_RIGHT_UP, self::OPEN_LEFT_BOTTOM, self::OPEN_RIGHT_BOTTOM,
            self::BEVEL_OPEN, self::ANTIBEVEL_OPEN, self::CLOCKWISE_OPEN, self::ANTICLOCKWISE_OPEN,
            self::OPEN_TOP_TO_BOTTOM, self::SECTOR_EXPAND => 'Opening',
            self::MOVE_LEFT, self::MOVE_RIGHT, self::MOVE_UP, self::MOVE_DOWN => 'Movement',
            self::SCROLL_UP, self::SCROLL_LEFT, self::SCROLL_RIGHT, self::SCROLL_DOWN,
            self::CONTINUOUS_SCROLL_LEFT, self::CONTINUOUS_SCROLL_RIGHT, self::CONTINUOUS_SCROLL_UP,
            self::CONTINUOUS_SCROLL_DOWN, self::SCROLL_LEFT_TO_RIGHT => 'Scrolling',
            self::FLICKER, self::FLICKER_H, self::FLICKER_V => 'Flicker',
            self::SHUTTER_V, self::SHUTTER_H => 'Shutter',
            self::WINDMILL, self::WINDMILL_ANTI => 'Windmill',
            self::RECTANGLE_FORTH, self::RECTANGLE_ENTAD, self::QUADRANGLE_FORTH, self::QUADRANGLE_ENTAD,
            self::CIRCLE_FORTH, self::CIRCLE_ENTAD => 'Geometric',
            self::ENTER_LEFT_UP, self::ENTER_RIGHT_UP, self::ENTER_LEFT_BOTTOM, self::ENTER_RIGHT_BOTTOM,
            self::BEVEL_ENTER, self::ANTIBEVEL_ENTER => 'Entering',
            self::ZEBRA_H, self::ZEBRA_V, self::ZEBRA_CROSSING_H, self::ZEBRA_CROSSING_V,
            self::CHESSBOARD_H, self::CHESSBOARD_V => 'Pattern',
            self::MOSAIC_BIG, self::MOSAIC_SMALL => 'Mosaic',
            self::RADIATION_UP, self::RADIATION_DOWN => 'Radiation',
            self::AMASS, self::DROP, self::COMBINATION_H, self::COMBINATION_V, self::BACKOUT,
            self::SCREWING, self::SNOW, self::GRADUAL_BIGGER_UP, self::GRADUAL_SMALLER_DOWN,
            self::GRADUAL_BIGGER_VERTICAL => 'Special',
            self::RANDOM => 'Random',
        };
    }

    /**
     * Check if effect is continuous.
     */
    public function isContinuous(): bool
    {
        return match ($this) {
            self::CONTINUOUS_SCROLL_LEFT, self::CONTINUOUS_SCROLL_RIGHT,
            self::CONTINUOUS_SCROLL_UP, self::CONTINUOUS_SCROLL_DOWN => true,
            default => false,
        };
    }

    /**
     * Get basic effects.
     *
     * @return array<int, self> Array of basic effects
     */
    public static function getBasicEffects(): array
    {
        return [
            self::DRAW,
            self::SCROLL_LEFT,
            self::SCROLL_RIGHT,
            self::SCROLL_UP,
            self::SCROLL_DOWN,
            self::FLICKER,
            self::MOVE_LEFT,
            self::MOVE_RIGHT,
        ];
    }

    /**
     * Get all effects by category.
     *
     * @param string $category The category to filter by
     *
     * @return array<int, self> Array of effects in the specified category
     */
    public static function getEffectsByCategory(string $category): array
    {
        $effects = [];
        foreach (self::cases() as $effect) {
            if ($effect->getCategory() === $category) {
                $effects[] = $effect;
            }
        }

        return $effects;
    }

    /**
     * Get all effects (for backward compatibility).
     *
     * @return array<int, string> Array mapping effect values to effect names
     */
    public static function getAllEffects(): array
    {
        $effects = [];
        foreach (self::cases() as $effect) {
            $effects[$effect->value] = $effect->getName();
        }

        return $effects;
    }
}
