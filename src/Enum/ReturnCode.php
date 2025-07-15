<?php

namespace LEDController\Enum;

/**
 * Return code enumeration
 */
enum ReturnCode: int
{
    case SUCCESS = 0x00;
    case INVALID_COMMAND = 0x01;
    case INVALID_PARAMETER = 0x02;
    case OPERATION_FAILED = 0x03;
    case CHECKSUM_ERROR = 0x04;
    case TIMEOUT = 0x05;
    case UNSUPPORTED = 0x06;
    case UNKNOWN_ERROR = 0xFF;

    /**
     * Get return code message
     */
    public function getMessage(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::INVALID_COMMAND => 'Invalid command',
            self::INVALID_PARAMETER => 'Invalid parameter',
            self::OPERATION_FAILED => 'Operation failed',
            self::CHECKSUM_ERROR => 'Checksum error',
            self::TIMEOUT => 'Timeout',
            self::UNSUPPORTED => 'Unsupported operation',
            self::UNKNOWN_ERROR => 'Unknown error',
        };
    }

    /**
     * Check if return code indicates success
     */
    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

    /**
     * Check if return code indicates an error
     */
    public function isError(): bool
    {
        return $this !== self::SUCCESS;
    }

    /**
     * Get severity level
     */
    public function getSeverity(): string
    {
        return match ($this) {
            self::SUCCESS => 'success',
            self::INVALID_COMMAND, self::INVALID_PARAMETER => 'warning',
            self::OPERATION_FAILED, self::CHECKSUM_ERROR, self::TIMEOUT, self::UNSUPPORTED => 'error',
            self::UNKNOWN_ERROR => 'critical',
        };
    }

    /**
     * Get return code from integer value
     */
    public static function fromInt(int $code): self
    {
        return match ($code) {
            0x00 => self::SUCCESS,
            0x01 => self::INVALID_COMMAND,
            0x02 => self::INVALID_PARAMETER,
            0x03 => self::OPERATION_FAILED,
            0x04 => self::CHECKSUM_ERROR,
            0x05 => self::TIMEOUT,
            0x06 => self::UNSUPPORTED,
            default => self::UNKNOWN_ERROR,
        };
    }

    /**
     * Get return code message (for backward compatibility)
     */
    public static function getReturnCodeMessage(int $code): string
    {
        return self::fromInt($code)->getMessage();
    }
}
