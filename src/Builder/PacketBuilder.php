<?php

namespace LEDController\Builder;

use LEDController\Enum\Command;
use LEDController\Enum\FontSize;
use LEDController\Enum\Effect;
use LEDController\Enum\Color;
use LEDController\Enum\Alignment;
use LEDController\Enum\WindowType;
use LEDController\Enum\ImageMode;

/**
 * Packet builder with modern enum support
 */
class PacketBuilder
{
    private readonly int $controllerId;
    private array $packets = [];

    public function __construct(int $controllerId = 1)
    {
        if ($controllerId < 1 || $controllerId > 255) {
            throw new \InvalidArgumentException("Controller ID must be between 1 and 255, got: $controllerId");
        }

        $this->controllerId = $controllerId;
    }

    /**
     * Create basic protocol packet
     */
    public function createBasicPacket(Command|int $command, string $data = ''): array
    {
        $cmdCode = ($command instanceof Command) ? $command->value : $command;

        $packet = [
            'start' => pack('C', 0x55),
            'id' => pack('C', $this->controllerId),
            'command' => pack('C', $cmdCode),
            'length' => pack('v', strlen($data)),
            'data' => $data
        ];

        // Calculate checksum
        $checksum = 0;
        foreach ($packet as $key => $value) {
            if ($key !== 'start') {
                for ($i = 0; $i < strlen($value); $i++) {
                    $checksum += ord($value[$i]);
                }
            }
        }
        $packet['checksum'] = pack('v', $checksum & 0xFFFF);

        return $packet;
    }

    /**
     * Create external calls packet
     */
    public function createExternalCallsPacket(int $subCommand, string $data = ''): array
    {
        return $this->createBasicPacket(Command::EXTERNAL_CALLS, pack('C', $subCommand) . $data);
    }

    /**
     * Create text display packet (modern enum support)
     */
    public function createTextDisplayPacket(
        int $x,
        int $y,
        int $width,
        int $height,
        string $text,
        FontSize|int $fontSize = FontSize::FONT_16,
        Color|string|array|int $color = Color::WHITE,
        Alignment|int $alignment = Alignment::LEFT
    ): array {
        $fontCode = ($fontSize instanceof FontSize) ? $fontSize->value : $fontSize;
        $colorRgb = Color::convert($color);
        $alignCode = ($alignment instanceof Alignment) ? $alignment->value : $alignment;

        $data = pack(
            'vvvvCCCCCCCCCC',
            $x,
            $y,
            $width,
            $height,
            $fontCode,
            $colorRgb['r'],
            $colorRgb['g'],
            $colorRgb['b'],
            $alignCode,
            0,
            0,
            0,
            0,
            0 // Reserved bytes
        ) . $text;

        return $this->createExternalCallsPacket(0x02, $data);
    }

    /**
     * Create window packet (modern enum support)
     */
    public function createWindowPacket(
        int $x,
        int $y,
        int $width,
        int $height,
        WindowType|int $windowType = WindowType::TEXT,
        Effect|int $effect = Effect::DRAW,
        int $speed = 5,
        int $stayTime = 10
    ): array {
        $windowCode = ($windowType instanceof WindowType) ? $windowType->value : $windowType;
        $effectCode = ($effect instanceof Effect) ? $effect->value : $effect;

        $data = pack(
            'vvvvCCCC',
            $x,
            $y,
            $width,
            $height,
            $windowCode,
            $effectCode,
            $speed,
            $stayTime
        );

        return $this->createExternalCallsPacket(0x01, $data);
    }

    /**
     * Create image display packet (modern enum support)
     */
    public function createImageDisplayPacket(
        int $x,
        int $y,
        int $width,
        int $height,
        string $imageData,
        ImageMode|int $imageMode = ImageMode::CENTER
    ): array {
        $modeCode = ($imageMode instanceof ImageMode) ? $imageMode->value : $imageMode;

        $data = pack('vvvvC', $x, $y, $width, $height, $modeCode) . $imageData;

        return $this->createExternalCallsPacket(0x03, $data);
    }

    /**
     * Create clock display packet (modern enum support)
     */
    public function createClockDisplayPacket(
        int $x,
        int $y,
        int $width,
        int $height,
        FontSize|int $fontSize = FontSize::FONT_16,
        Color|string|array|int $color = Color::WHITE,
        int $format = 0x3F // Show all date/time components
    ): array {
        $fontCode = ($fontSize instanceof FontSize) ? $fontSize->value : $fontSize;
        $colorRgb = Color::convert($color);

        $data = pack(
            'vvvvCCCCCCCCC',
            $x,
            $y,
            $width,
            $height,
            $fontCode,
            $colorRgb['r'],
            $colorRgb['g'],
            $colorRgb['b'],
            $format,
            0,
            0,
            0,
            0 // Reserved bytes
        );

        return $this->createExternalCallsPacket(0x05, $data);
    }

    /**
     * Create save data packet
     */
    public function createSaveDataPacket(): array
    {
        return $this->createExternalCallsPacket(0x06);
    }

    /**
     * Create exit split screen packet
     */
    public function createExitSplitScreenPacket(): array
    {
        return $this->createExternalCallsPacket(0x07);
    }

    /**
     * Create brightness control packet
     */
    public function createBrightnessPacket(int $brightness): array
    {
        if ($brightness < 0 || $brightness > 100) {
            throw new \InvalidArgumentException("Brightness must be between 0 and 100, got: $brightness");
        }

        // Convert to 0-255 scale
        $level = (int) ($brightness * 255 / 100);
        $data = pack('C', $level);

        return $this->createBasicPacket(Command::BRIGHTNESS_QUERY_SET, $data);
    }

    /**
     * Create time set packet
     */
    public function createTimeSetPacket(?\DateTime $dateTime = null): array
    {
        $dateTime = $dateTime ?? new \DateTime();

        $data = pack(
            'CCCCCCCC',
            $dateTime->format('y'), // Year (2 digits)
            $dateTime->format('n'),  // Month
            $dateTime->format('j'),  // Day
            $dateTime->format('G'),  // Hour (24-hour format)
            $dateTime->format('i'),  // Minute
            $dateTime->format('s'),  // Second
            $dateTime->format('w'),  // Day of week (0=Sunday)
            0 // Reserved
        );

        return $this->createBasicPacket(Command::TIME_QUERY_SET, $data);
    }

    /**
     * Create power control packet
     */
    public function createPowerControlPacket(bool $powerOn): array
    {
        $data = pack('C', $powerOn ? 1 : 0);
        return $this->createBasicPacket(Command::POWER_CONTROL, $data);
    }

    /**
     * Create query packet
     */
    public function createQueryPacket(Command|int $queryCommand): array
    {
        $cmdCode = ($queryCommand instanceof Command) ? $queryCommand->value : $queryCommand;

        if (
            !in_array($cmdCode, [
            Command::QUERY_VERSION->value,
            Command::POWER_INFO->value,
            Command::QUERY_TEMPERATURE->value,
            Command::QUERY_DISK_SPACE->value,
            Command::TIME_QUERY_SET->value,
            Command::BRIGHTNESS_QUERY_SET->value
            ])
        ) {
            throw new \InvalidArgumentException("Command is not a query command");
        }

        return $this->createBasicPacket($cmdCode);
    }

    /**
     * Create restart packet
     */
    public function createRestartPacket(bool $hardwareRestart = false): array
    {
        $command = $hardwareRestart ? Command::RESTART_HARDWARE : Command::RESTART_APP;
        return $this->createBasicPacket($command);
    }

    /**
     * Get all packets
     */
    public function getPackets(): array
    {
        return $this->packets;
    }

    /**
     * Add packet to collection
     */
    public function addPacket(array $packet): self
    {
        $this->packets[] = $packet;
        return $this;
    }

    /**
     * Clear all packets
     */
    public function clearPackets(): self
    {
        $this->packets = [];
        return $this;
    }

    /**
     * Get controller ID
     */
    public function getControllerId(): int
    {
        return $this->controllerId;
    }

    /**
     * Convert packet to binary string
     */
    public function packetToBinary(array $packet): string
    {
        $binary = '';
        foreach ($packet as $segment) {
            $binary .= $segment;
        }
        return $binary;
    }

    /**
     * Convert all packets to binary
     */
    public function allPacketsToBinary(): array
    {
        $binaries = [];
        foreach ($this->packets as $packet) {
            $binaries[] = $this->packetToBinary($packet);
        }
        return $binaries;
    }
}
