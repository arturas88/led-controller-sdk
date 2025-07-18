<?php

declare(strict_types=1);

namespace LEDController\Communication;

use LEDController\Exception\CommunicationException;
use LEDController\Exception\ConnectionException;
use LEDController\Exception\ProtocolException;
use LEDController\Exception\TimeoutException;
use LEDController\Interface\CommunicationInterface;
use LEDController\Packet;
use LEDController\Response;

/**
 * Serial communication implementation.
 */
class SerialCommunication implements CommunicationInterface
{
    /**
     * @var array<string, mixed> Serial configuration
     */
    private array $config;

    /** @phpstan-ignore-next-line */
    private $handle = null;

    /**
     * @param array<string, mixed> $config Serial configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): bool
    {
        // Platform-specific serial port handling
        $port = $this->config['serialPort'];

        if (PHP_OS_FAMILY === 'Windows') {
            $this->handle = @fopen($port, 'r+');
        } else {
            // Unix-like systems
            $this->handle = @fopen("/dev/{$port}", 'r+');

            if ($this->handle) {
                // Configure serial port
                $cmd = \sprintf(
                    'stty -F /dev/%s %d cs8 -cstopb -parenb raw',
                    escapeshellarg($port),
                    $this->config['baudRate'],
                );
                exec($cmd);
            }
        }

        if (!$this->handle) {
            throw new ConnectionException("Failed to open serial port: {$port}");
        }

        // Set stream timeout
        stream_set_timeout($this->handle, (int) ($this->config['timeout'] / 1000));

        return true;
    }

    public function disconnect(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function send(Packet $packet): Response
    {
        if (!$this->isConnected()) {
            throw new ConnectionException('Not connected');
        }

        // Build packet with RS232/485 framing
        $data = $packet->buildSerial();

        // Send data
        $written = fwrite($this->handle, $data);

        if ($written === false) {
            throw new CommunicationException('Failed to write to serial port');
        }

        // Read response
        $responseData = $this->readSerialResponse();

        return Response::parseSerial($responseData);
    }

    public function isConnected(): bool
    {
        return $this->handle !== null;
    }

    private function readSerialResponse(): string
    {
        $data = '';
        $inPacket = false;
        $escaped = false;

        while (true) {
            $byte = fread($this->handle, 1);

            if ($byte === false || $byte === '') {
                $info = stream_get_meta_data($this->handle);
                if ($info['timed_out']) {
                    throw new TimeoutException('Timeout reading serial response');
                }

                continue;
            }

            $byteVal = \ord($byte);

            if (!$inPacket && $byteVal === 0xA5) {
                // Start of packet
                $inPacket = true;
                $data = '';

                continue;
            }

            if ($inPacket) {
                if ($escaped) {
                    // Handle escaped bytes
                    switch ($byteVal) {
                        case 0x05:
                            $data .= \chr(0xA5);

                            break;

                        case 0x0A:
                            $data .= \chr(0xAA);

                            break;

                        case 0x0E:
                            $data .= \chr(0xAE);

                            break;

                        default:
                            throw new ProtocolException('Invalid escape sequence: 0xAA 0x' . dechex($byteVal));
                    }
                    $escaped = false;
                } else {
                    if ($byteVal === 0xAA) {
                        $escaped = true;
                    } elseif ($byteVal === 0xAE) {
                        // End of packet
                        return $data;
                    } else {
                        $data .= $byte;
                    }
                }
            }
        }
    }
}
