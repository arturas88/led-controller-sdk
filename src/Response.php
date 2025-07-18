<?php

declare(strict_types=1);

namespace LEDController;

use LEDController\Exception\ChecksumException;
use LEDController\Exception\ProtocolException;

/**
 * Response class for parsing protocol responses.
 */
class Response
{
    private int $packetType;

    private int $cardType;

    private int $cardId;

    private int $command;

    private int $returnCode = 0;

    private string $data = '';

    private bool $valid = false;

    /**
     * Parse network response.
     */
    public static function parse(string $rawData): self
    {
        $response = new self();

        if (\strlen($rawData) < 7) {
            throw new ProtocolException('Response too short');
        }

        // Parse header
        $response->packetType = \ord($rawData[0]);
        $response->cardType = \ord($rawData[1]);
        $response->cardId = \ord($rawData[2]);
        $response->command = \ord($rawData[3]);
        $response->returnCode = \ord($rawData[4]);

        // Extract data based on command type
        if ($response->command === 0x7B) {
            // External calls protocol response
            if (\strlen($rawData) < 11) {
                throw new ProtocolException('Invalid external protocol response');
            }

            $dataLength = unpack('v', substr($rawData, 5, 2))[1];

            if (\strlen($rawData) < 11 + $dataLength) {
                throw new ProtocolException('Response data incomplete');
            }

            $response->data = substr($rawData, 11, $dataLength);
        } else {
            // Basic protocol response
            $checksumPos = \strlen($rawData) - 2;

            if ($checksumPos > 5) {
                $response->data = substr($rawData, 5, $checksumPos - 5);
            }
        }

        // Verify checksum - try both endianness formats
        $checksumPos = \strlen($rawData) - 2;
        $checksumBytes = substr($rawData, $checksumPos, 2);
        $expectedChecksumLE = unpack('v', $checksumBytes)[1]; // Little-endian
        $expectedChecksumBE = unpack('n', $checksumBytes)[1]; // Big-endian

        $calculatedChecksum = 0;
        for ($i = 0; $i < $checksumPos; $i++) {
            $calculatedChecksum += \ord($rawData[$i]);
            $calculatedChecksum &= 0xFFFF;
        }

        if ($calculatedChecksum !== $expectedChecksumLE && $calculatedChecksum !== $expectedChecksumBE) {
            throw new ChecksumException(
                \sprintf(
                    'Checksum mismatch: calculated %04X, expected %04X (LE) / %04X (BE)',
                    $calculatedChecksum,
                    $expectedChecksumLE,
                    $expectedChecksumBE,
                ),
            );
        }

        $response->valid = true;

        return $response;
    }

    /**
     * Parse serial response (with transcoding already handled).
     */
    public static function parseSerial(string $rawData): self
    {
        // Serial response has already been decoded, just parse it
        return self::parse($rawData);
    }

    public function isSuccess(): bool
    {
        return $this->valid && $this->returnCode === 0x00;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    public function getReturnCodeMessage(): string
    {
        $messages = [
            0x00 => 'Success',
            0x01 => 'Checksum error',
            0x02 => 'Packet sequence error',
            0x03 => 'Invalid parameter',
            0x04 => 'Operation failed',
            0x10 => 'Unknown command',
            0x11 => 'Program number out of range',
            0x12 => 'Window number out of range',
            0x13 => 'Window definition outside screen size',
            0x80 => 'Not in program template mode',
        ];

        return $messages[$this->returnCode] ?? 'Unknown error';
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCommand(): int
    {
        return $this->command;
    }

    /**
     * Parse time from response data.
     */
    public function getDateTime(): \DateTime
    {
        if (\strlen($this->data) < 7) {
            throw new ProtocolException('Invalid time data');
        }

        $second = \ord($this->data[1]);
        $minute = \ord($this->data[2]);
        $hour = \ord($this->data[3]);
        $weekday = \ord($this->data[4]); // 0=Sunday, 1=Monday, etc.
        $day = \ord($this->data[5]);
        $month = \ord($this->data[6]);
        $year = \ord($this->data[7]) + 2000; // 2-digit year

        return new \DateTime(\sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d',
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
        ));
    }

    /**
     * Parse temperature from response data.
     *
     * @return array<string, mixed> Temperature data with celsius, fahrenheit, and humidity
     */
    public function getTemperature(): array
    {
        if (\strlen($this->data) < 8) {
            throw new ProtocolException('Invalid temperature data');
        }

        $result = [];

        // Parse Celsius temperature
        $tempBytes = unpack('C*', substr($this->data, 1, 2));
        $sign = ($tempBytes[1] & 0x80) ? -1 : 1;
        $integerPart = (($tempBytes[1] & 0x7F) << 4) | (($tempBytes[2] & 0xF0) >> 4);
        $fractionalPart = ($tempBytes[2] & 0x0F) * 0.0625;
        $result['celsius'] = $sign * ($integerPart + $fractionalPart);

        // Parse Fahrenheit temperature
        $tempBytes = unpack('C*', substr($this->data, 3, 2));
        $sign = ($tempBytes[1] & 0x80) ? -1 : 1;
        $integerPart = (($tempBytes[1] & 0x7F) << 4) | (($tempBytes[2] & 0xF0) >> 4);
        $fractionalPart = ($tempBytes[2] & 0x0F) * 0.0625;
        $result['fahrenheit'] = $sign * ($integerPart + $fractionalPart);

        // Parse humidity if available
        if (\strlen($this->data) >= 11) {
            $result['humidity'] = \ord($this->data[10]);
        }

        return $result;
    }

    /**
     * Parse version info from response data.
     *
     * @return array<string, mixed> Version information data
     */
    public function getVersionInfo(): array
    {
        if (\strlen($this->data) < 8) {
            throw new ProtocolException('Invalid version data');
        }

        $info = [];
        $info['cardType'] = \ord($this->data[1]);

        $logic = \ord($this->data[2]);
        $info['logicVersion'] = \sprintf('%d.%d', ($logic >> 4) & 0x0F, $logic & 0x0F);

        $bios = \ord($this->data[3]);
        $info['biosVersion'] = \sprintf('%d.%d', ($bios >> 4) & 0x0F, $bios & 0x0F);

        $app = \ord($this->data[7]);
        $info['appVersion'] = \sprintf('%d.%d', ($app >> 4) & 0x0F, $app & 0x0F);

        return $info;
    }

    /**
     * Parse free disk space from response data.
     */
    public function getFreeSpace(): int
    {
        if (\strlen($this->data) < 5) {
            throw new ProtocolException('Invalid disk space data');
        }

        return unpack('V', substr($this->data, 1, 4))[1]; // Little-endian 32-bit
    }

    /**
     * Parse brightness values from response data.
     *
     * @return array<int, int> Brightness values for each hour (0-23)
     */
    public function getBrightnessValues(): array
    {
        if (\strlen($this->data) < 25) {
            throw new ProtocolException('Invalid brightness data');
        }

        $values = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $values[$hour] = \ord($this->data[1 + $hour]);
        }

        return $values;
    }
}
