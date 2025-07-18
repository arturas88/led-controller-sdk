<?php

declare(strict_types=1);

namespace LEDController\Enum;

/**
 * Baud rate enumeration.
 */
enum BaudRate: int
{
    case BAUD_9600 = 0;
    case BAUD_19200 = 1;
    case BAUD_38400 = 2;
    case BAUD_57600 = 3;
    case BAUD_115200 = 4;

    /**
     * Get actual baud rate value.
     */
    public function getRate(): int
    {
        return match ($this) {
            self::BAUD_9600 => 9600,
            self::BAUD_19200 => 19200,
            self::BAUD_38400 => 38400,
            self::BAUD_57600 => 57600,
            self::BAUD_115200 => 115200,
        };
    }

    /**
     * Get baud rate name.
     */
    public function getName(): string
    {
        return match ($this) {
            self::BAUD_9600 => '9600 bps',
            self::BAUD_19200 => '19200 bps',
            self::BAUD_38400 => '38400 bps',
            self::BAUD_57600 => '57600 bps',
            self::BAUD_115200 => '115200 bps',
        };
    }

    /**
     * Get baud rate from actual rate value.
     */
    public static function fromRate(int $rate): self
    {
        return match ($rate) {
            9600 => self::BAUD_9600,
            19200 => self::BAUD_19200,
            38400 => self::BAUD_38400,
            57600 => self::BAUD_57600,
            115200 => self::BAUD_115200,
            default => self::BAUD_115200,
        };
    }

    /**
     * Get fastest baud rate.
     */
    public static function fastest(): self
    {
        return self::BAUD_115200;
    }

    /**
     * Get slowest baud rate.
     */
    public static function slowest(): self
    {
        return self::BAUD_9600;
    }

    /**
     * Get all available baud rates.
     *
     * @return array<int, int> Array of all available baud rates
     */
    public static function getAllRates(): array
    {
        return [
            self::BAUD_9600->getRate(),
            self::BAUD_19200->getRate(),
            self::BAUD_38400->getRate(),
            self::BAUD_57600->getRate(),
            self::BAUD_115200->getRate(),
        ];
    }

    /**
     * Get baud rate by code (for backward compatibility).
     */
    public static function getBaudRate(int $code): int
    {
        return match ($code) {
            0 => 9600,
            1 => 19200,
            2 => 38400,
            3 => 57600,
            4 => 115200,
            default => 115200,
        };
    }
}
