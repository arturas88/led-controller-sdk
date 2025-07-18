<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Command enumeration for protocol commands.
 */
enum Command: int
{
    // Basic protocol commands
    case RESTART_HARDWARE = 0x2D;
    case RESTART_APP = 0xFE;
    case WRITE_FILE_OPEN = 0x30;
    case WRITE_FILE_WRITE = 0x32;
    case WRITE_FILE_CLOSE = 0x33;
    case QUICK_WRITE_FILE_OPEN = 0x50;
    case QUICK_WRITE_FILE_WRITE = 0x51;
    case QUICK_WRITE_FILE_CLOSE = 0x52;
    case TIME_QUERY_SET = 0x47;
    case BRIGHTNESS_QUERY_SET = 0x46;
    case QUERY_VERSION = 0x2E;
    case POWER_INFO = 0x45;
    case POWER_CONTROL = 0x76;
    case QUERY_TEMPERATURE = 0x75;
    case REMOVE_FILE = 0x2C;
    case QUERY_DISK_SPACE = 0x29;

    // Setup protocol commands
    case NETWORK_SETUP = 0x3C;
    case ID_BAUD_SETUP = 0x3E;

    // External calls protocol
    case EXTERNAL_CALLS = 0x7B;

    // Template commands
    case TEMPLATE_CREATE = 0x81;
    case TEMPLATE_SEND_TEXT = 0x85;
    case TEMPLATE_SEND_IMAGE = 0x86;
    case TEMPLATE_PLAY_CONTROL = 0x8B;
    case TEMPLATE_PLAY_PROGRAM = 0x8C;
    case TEMPLATE_PLAY_PLAYBILL = 0x8D;
    case TEMPLATE_DELETE = 0x84;
    case TEMPLATE_PROPERTY = 0x8A;

    /**
     * Get command name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::RESTART_HARDWARE => 'Restart Hardware',
            self::RESTART_APP => 'Restart Application',
            self::WRITE_FILE_OPEN => 'Write File Open',
            self::WRITE_FILE_WRITE => 'Write File Write',
            self::WRITE_FILE_CLOSE => 'Write File Close',
            self::QUICK_WRITE_FILE_OPEN => 'Quick Write File Open',
            self::QUICK_WRITE_FILE_WRITE => 'Quick Write File Write',
            self::QUICK_WRITE_FILE_CLOSE => 'Quick Write File Close',
            self::TIME_QUERY_SET => 'Time Query/Set',
            self::BRIGHTNESS_QUERY_SET => 'Brightness Query/Set',
            self::QUERY_VERSION => 'Query Version',
            self::POWER_INFO => 'Power Info',
            self::POWER_CONTROL => 'Power Control',
            self::QUERY_TEMPERATURE => 'Query Temperature',
            self::REMOVE_FILE => 'Remove File',
            self::QUERY_DISK_SPACE => 'Query Disk Space',
            self::NETWORK_SETUP => 'Network Setup',
            self::ID_BAUD_SETUP => 'ID/Baud Setup',
            self::EXTERNAL_CALLS => 'External Calls',
            self::TEMPLATE_CREATE => 'Template Create',
            self::TEMPLATE_SEND_TEXT => 'Template Send Text',
            self::TEMPLATE_SEND_IMAGE => 'Template Send Image',
            self::TEMPLATE_PLAY_CONTROL => 'Template Play Control',
            self::TEMPLATE_PLAY_PROGRAM => 'Template Play Program',
            self::TEMPLATE_PLAY_PLAYBILL => 'Template Play Playbill',
            self::TEMPLATE_DELETE => 'Template Delete',
            self::TEMPLATE_PROPERTY => 'Template Property',
        };
    }

    /**
     * Get command category.
     */
    public function getCategory(): string
    {
        return match ($this) {
            self::RESTART_HARDWARE, self::RESTART_APP => 'System',
            self::WRITE_FILE_OPEN, self::WRITE_FILE_WRITE, self::WRITE_FILE_CLOSE,
            self::QUICK_WRITE_FILE_OPEN, self::QUICK_WRITE_FILE_WRITE, self::QUICK_WRITE_FILE_CLOSE,
            self::REMOVE_FILE, self::QUERY_DISK_SPACE => 'File',
            self::TIME_QUERY_SET, self::BRIGHTNESS_QUERY_SET, self::QUERY_VERSION,
            self::POWER_INFO, self::POWER_CONTROL, self::QUERY_TEMPERATURE => 'Query',
            self::NETWORK_SETUP, self::ID_BAUD_SETUP => 'Setup',
            self::EXTERNAL_CALLS => 'External',
            self::TEMPLATE_CREATE, self::TEMPLATE_SEND_TEXT, self::TEMPLATE_SEND_IMAGE,
            self::TEMPLATE_PLAY_CONTROL, self::TEMPLATE_PLAY_PROGRAM, self::TEMPLATE_PLAY_PLAYBILL,
            self::TEMPLATE_DELETE, self::TEMPLATE_PROPERTY => 'Template',
        };
    }

    /**
     * Check if command requires data.
     */
    public function requiresData(): bool
    {
        return match ($this) {
            self::WRITE_FILE_WRITE, self::QUICK_WRITE_FILE_WRITE, self::TIME_QUERY_SET,
            self::BRIGHTNESS_QUERY_SET, self::POWER_CONTROL, self::NETWORK_SETUP,
            self::ID_BAUD_SETUP, self::EXTERNAL_CALLS, self::TEMPLATE_CREATE,
            self::TEMPLATE_SEND_TEXT, self::TEMPLATE_SEND_IMAGE, self::TEMPLATE_PROPERTY => true,
            default => false,
        };
    }

    /**
     * Check if command returns data.
     */
    public function returnsData(): bool
    {
        return match ($this) {
            self::QUERY_VERSION, self::POWER_INFO, self::QUERY_TEMPERATURE,
            self::QUERY_DISK_SPACE, self::TIME_QUERY_SET, self::BRIGHTNESS_QUERY_SET => true,
            default => false,
        };
    }

    /**
     * Get all file-related commands.
     *
     * @return array<int, Command> Array of file-related commands
     */
    public static function getFileCommands(): array
    {
        return [
            self::WRITE_FILE_OPEN,
            self::WRITE_FILE_WRITE,
            self::WRITE_FILE_CLOSE,
            self::QUICK_WRITE_FILE_OPEN,
            self::QUICK_WRITE_FILE_WRITE,
            self::QUICK_WRITE_FILE_CLOSE,
            self::REMOVE_FILE,
            self::QUERY_DISK_SPACE,
        ];
    }

    /**
     * Get all template commands.
     *
     * @return array<int, Command> Array of template commands
     */
    public static function getTemplateCommands(): array
    {
        return [
            self::TEMPLATE_CREATE,
            self::TEMPLATE_SEND_TEXT,
            self::TEMPLATE_SEND_IMAGE,
            self::TEMPLATE_PLAY_CONTROL,
            self::TEMPLATE_PLAY_PROGRAM,
            self::TEMPLATE_PLAY_PLAYBILL,
            self::TEMPLATE_DELETE,
            self::TEMPLATE_PROPERTY,
        ];
    }
}
