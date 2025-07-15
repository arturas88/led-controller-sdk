<?php

namespace LEDController\Enum;

/**
 * Protocol constants enumeration
 *
 * Contains various protocol-specific constants not covered by other enums
 */
class Protocol
{
    // =========================================================================
    // COLOR MODE CONSTANTS
    // =========================================================================

    public const COLOR_MODE_1BIT = 0;
    public const COLOR_MODE_3BIT = 1;
    public const COLOR_FULL = 1;

    // =========================================================================
    // CLOCK FORMAT CONSTANTS
    // =========================================================================

    public const CLOCK_12_HOUR = 0;
    public const CLOCK_24_HOUR = 1;
    public const CLOCK_YEAR_4_DIGIT = 0;
    public const CLOCK_YEAR_2_DIGIT = 1;
    public const CLOCK_SINGLE_ROW = 0;
    public const CLOCK_MULTI_ROW = 1;

    // Clock content constants
    public const CLOCK_SHOW_YEAR = 0x01;
    public const CLOCK_SHOW_MONTH = 0x02;
    public const CLOCK_SHOW_DAY = 0x04;
    public const CLOCK_SHOW_HOUR = 0x08;
    public const CLOCK_SHOW_MINUTE = 0x10;
    public const CLOCK_SHOW_SECOND = 0x20;
    public const CLOCK_SHOW_WEEKDAY = 0x40;

    // =========================================================================
    // TEMPERATURE FORMAT CONSTANTS
    // =========================================================================

    public const TEMP_CELSIUS = 0;
    public const TEMP_FAHRENHEIT = 1;
    public const TEMP_SHOW_HUMIDITY = 0x01;
    public const TEMP_SHOW_UNIT = 0x02;

    // =========================================================================
    // VARIABLE TYPE CONSTANTS
    // =========================================================================

    public const VARIABLE_TYPE_TEXT = 0;
    public const VARIABLE_TYPE_NUMBER = 1;
    public const VARIABLE_TYPE_DATETIME = 2;

    // =========================================================================
    // EXTERNAL CALLS CONSTANTS
    // =========================================================================

    public const EXT_SPLIT_SCREEN = 0x01;
    public const EXT_TEXT_DISPLAY = 0x02;
    public const EXT_IMAGE_DISPLAY = 0x03;
    public const EXT_RICH_TEXT_DISPLAY = 0x04;
    public const EXT_CLOCK_DISPLAY = 0x05;
    public const EXT_SAVE_DATA = 0x06;
    public const EXT_EXIT_SPLIT_SCREEN = 0x07;
    public const EXT_PLAY_PROGRAM = 0x08;
    public const EXT_PLAY_PROGRAM_WINDOW = 0x09;
    public const EXT_SET_VARIABLE = 0x0A;
    public const EXT_QUERY_VARIABLE = 0x0B;
    public const EXT_GLOBAL_ZONE_DISPLAY = 0x0C;
    public const EXT_QUERY_VARIABLE_LIST = 0x0D;
    public const EXT_TIMER_CONTROL = 0x0E;
    public const EXT_PLAY_GLOBAL_ZONE = 0x0F;
    public const EXT_COUNTDOWN_DISPLAY = 0x10;
    public const EXT_PURE_TEXT_DISPLAY = 0x12;

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get color mode name
     */
    public static function getColorModeName(int $mode): string
    {
        return match ($mode) {
            self::COLOR_MODE_1BIT => '1-bit (2 colors)',
            self::COLOR_MODE_3BIT => '3-bit (8 colors)',
            self::COLOR_FULL => 'Full color (24-bit)',
            default => 'Unknown color mode',
        };
    }

    /**
     * Get clock format name
     */
    public static function getClockFormatName(int $format): string
    {
        return match ($format) {
            self::CLOCK_12_HOUR => '12-hour format',
            self::CLOCK_24_HOUR => '24-hour format',
            default => 'Unknown clock format',
        };
    }

    /**
     * Get temperature unit name
     */
    public static function getTemperatureUnitName(int $unit): string
    {
        return match ($unit) {
            self::TEMP_CELSIUS => 'Celsius',
            self::TEMP_FAHRENHEIT => 'Fahrenheit',
            default => 'Unknown temperature unit',
        };
    }

    /**
     * Get variable type name
     */
    public static function getVariableTypeName(int $type): string
    {
        return match ($type) {
            self::VARIABLE_TYPE_TEXT => 'Text',
            self::VARIABLE_TYPE_NUMBER => 'Number',
            self::VARIABLE_TYPE_DATETIME => 'DateTime',
            default => 'Unknown variable type',
        };
    }

    /**
     * Get external call command name
     */
    public static function getExternalCallName(int $command): string
    {
        return match ($command) {
            self::EXT_SPLIT_SCREEN => 'Split Screen',
            self::EXT_TEXT_DISPLAY => 'Text Display',
            self::EXT_IMAGE_DISPLAY => 'Image Display',
            self::EXT_RICH_TEXT_DISPLAY => 'Rich Text Display',
            self::EXT_CLOCK_DISPLAY => 'Clock Display',
            self::EXT_SAVE_DATA => 'Save Data',
            self::EXT_EXIT_SPLIT_SCREEN => 'Exit Split Screen',
            self::EXT_PLAY_PROGRAM => 'Play Program',
            self::EXT_PLAY_PROGRAM_WINDOW => 'Play Program Window',
            self::EXT_SET_VARIABLE => 'Set Variable',
            self::EXT_QUERY_VARIABLE => 'Query Variable',
            self::EXT_GLOBAL_ZONE_DISPLAY => 'Global Zone Display',
            self::EXT_QUERY_VARIABLE_LIST => 'Query Variable List',
            self::EXT_TIMER_CONTROL => 'Timer Control',
            self::EXT_PLAY_GLOBAL_ZONE => 'Play Global Zone',
            self::EXT_COUNTDOWN_DISPLAY => 'Countdown Display',
            self::EXT_PURE_TEXT_DISPLAY => 'Pure Text Display',
            default => 'Unknown external call command',
        };
    }

    /**
     * Get all external call commands
     */
    public static function getAllExternalCalls(): array
    {
        return [
            self::EXT_SPLIT_SCREEN => 'Split Screen',
            self::EXT_TEXT_DISPLAY => 'Text Display',
            self::EXT_IMAGE_DISPLAY => 'Image Display',
            self::EXT_RICH_TEXT_DISPLAY => 'Rich Text Display',
            self::EXT_CLOCK_DISPLAY => 'Clock Display',
            self::EXT_SAVE_DATA => 'Save Data',
            self::EXT_EXIT_SPLIT_SCREEN => 'Exit Split Screen',
            self::EXT_PLAY_PROGRAM => 'Play Program',
            self::EXT_PLAY_PROGRAM_WINDOW => 'Play Program Window',
            self::EXT_SET_VARIABLE => 'Set Variable',
            self::EXT_QUERY_VARIABLE => 'Query Variable',
            self::EXT_GLOBAL_ZONE_DISPLAY => 'Global Zone Display',
            self::EXT_QUERY_VARIABLE_LIST => 'Query Variable List',
            self::EXT_TIMER_CONTROL => 'Timer Control',
            self::EXT_PLAY_GLOBAL_ZONE => 'Play Global Zone',
            self::EXT_COUNTDOWN_DISPLAY => 'Countdown Display',
            self::EXT_PURE_TEXT_DISPLAY => 'Pure Text Display',
        ];
    }

    /**
     * Get all color modes
     */
    public static function getAllColorModes(): array
    {
        return [
            self::COLOR_MODE_1BIT => '1-bit (2 colors)',
            self::COLOR_MODE_3BIT => '3-bit (8 colors)',
            self::COLOR_FULL => 'Full color (24-bit)',
        ];
    }

    /**
     * Get all clock content flags
     */
    public static function getAllClockContentFlags(): array
    {
        return [
            self::CLOCK_SHOW_YEAR => 'Show Year',
            self::CLOCK_SHOW_MONTH => 'Show Month',
            self::CLOCK_SHOW_DAY => 'Show Day',
            self::CLOCK_SHOW_HOUR => 'Show Hour',
            self::CLOCK_SHOW_MINUTE => 'Show Minute',
            self::CLOCK_SHOW_SECOND => 'Show Second',
            self::CLOCK_SHOW_WEEKDAY => 'Show Weekday',
        ];
    }

    /**
     * Check if clock content flag is set
     */
    public static function isClockContentSet(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }

    /**
     * Create clock content flags
     */
    public static function createClockContentFlags(array $flags): int
    {
        $result = 0;
        foreach ($flags as $flag) {
            if (
                in_array(
                    $flag,
                    [
                        self::CLOCK_SHOW_YEAR,
                        self::CLOCK_SHOW_MONTH,
                        self::CLOCK_SHOW_DAY,
                        self::CLOCK_SHOW_HOUR,
                        self::CLOCK_SHOW_MINUTE,
                        self::CLOCK_SHOW_SECOND,
                        self::CLOCK_SHOW_WEEKDAY,
                    ]
                )
            ) {
                $result |= $flag;
            }
        }
        return $result;
    }
}
