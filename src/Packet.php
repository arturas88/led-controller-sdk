<?php

declare(strict_types=1);

namespace LEDController;

/**
 * Packet class for building protocol packets.
 */
class Packet
{
    private int $cardId;

    private int $command;

    private int $subCommand = 0;

    private string $data = '';

    private bool $requiresConfirmation = true;

    private int $packetType = 0x68; // Send packet type

    public function __construct(int $cardId, int $command)
    {
        $this->cardId = $cardId;
        $this->command = $command;
    }

    public function setSubCommand(int $subCommand): self
    {
        $this->subCommand = $subCommand;

        return $this;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setRequiresConfirmation(bool $requires): self
    {
        $this->requiresConfirmation = $requires;

        return $this;
    }

    /**
     * Build packet for network communication (without checksum).
     */
    public function build(): string
    {
        $packet = '';

        // Packet type
        $packet .= \chr($this->packetType);

        // Card type (fixed)
        $packet .= \chr(0x32);

        // Card ID
        $packet .= \chr($this->cardId);

        // Command code
        $packet .= \chr($this->command);

        // Additional information / confirmation mark
        $confirmMark = $this->requiresConfirmation ? 0x01 : 0x00;
        $packet .= \chr($confirmMark);

        // Add data based on command type
        if ($this->command === 0x7B) {
            // External calls protocol
            $dataLength = \strlen($this->data) + 1; // +1 for subcommand
            $packet .= pack('v', $dataLength); // Little-endian
            $packet .= \chr(0x00); // Packet number
            $packet .= \chr(0x00); // Last packet number
            $packet .= \chr($this->subCommand);
            $packet .= $this->data;
        } else {
            // Basic protocol
            $packet .= $this->data;
        }

        // Don't include checksum here - it's handled by communication layer
        return $packet;
    }

    /**
     * Build packet for network communication with checksum.
     */
    public function buildWithChecksum(): string
    {
        $packet = $this->build();

        // Calculate and add checksum
        $checksum = $this->calculateChecksum($packet);
        $packet .= pack('v', $checksum); // Little-endian

        return $packet;
    }

    /**
     * Build packet for serial communication with transcoding.
     */
    public function buildSerial(): string
    {
        $packet = $this->buildWithChecksum();

        // Add start code
        $encoded = \chr(0xA5);

        // Transcode the packet data
        for ($i = 0; $i < \strlen($packet); $i++) {
            $byte = \ord($packet[$i]);

            switch ($byte) {
                case 0xA5:
                    $encoded .= \chr(0xAA) . \chr(0x05);

                    break;

                case 0xAA:
                    $encoded .= \chr(0xAA) . \chr(0x0A);

                    break;

                case 0xAE:
                    $encoded .= \chr(0xAA) . \chr(0x0E);

                    break;

                default:
                    $encoded .= \chr($byte);
            }
        }

        // Add end code
        $encoded .= \chr(0xAE);

        return $encoded;
    }

    public function getCardId(): int
    {
        return $this->cardId;
    }

    public function getCommand(): int
    {
        return $this->command;
    }

    public function getSubCommand(): int
    {
        return $this->subCommand;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Calculate 16-bit checksum.
     */
    private function calculateChecksum(string $data): int
    {
        $checksum = 0;

        for ($i = 0; $i < \strlen($data); $i++) {
            $checksum += \ord($data[$i]);
            $checksum &= 0xFFFF; // Keep as 16-bit
        }

        return $checksum;
    }
}
