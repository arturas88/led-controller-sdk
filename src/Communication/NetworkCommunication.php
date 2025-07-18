<?php

declare(strict_types=1);

namespace LEDController\Communication;

use LEDController\Exception\CommunicationException;
use LEDController\Exception\ConnectionException;
use LEDController\Exception\TimeoutException;
use LEDController\Interface\CommunicationInterface;
use LEDController\Packet;
use LEDController\Response;

/**
 * Network communication implementation.
 */
class NetworkCommunication implements CommunicationInterface
{
    /**
     * @var array<string, mixed> Network configuration
     */
    private array $config;

    /** @phpstan-ignore-next-line */
    private $socket = null;

    /**
     * @param array<string, mixed> $config Network configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): bool
    {
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            throw new ConnectionException('Failed to create socket: ' . socket_strerror(socket_last_error()));
        }

        // Set non-blocking for connection test
        socket_set_nonblock($this->socket);

        $result = @socket_connect($this->socket, $this->config['ip'], $this->config['port']);

        if ($result === false) {
            $error = socket_last_error($this->socket);
            if ($error !== SOCKET_EINPROGRESS && $error !== SOCKET_EALREADY && $error !== SOCKET_EISCONN) {
                $errorMsg = socket_strerror($error);
                socket_close($this->socket);
                $this->socket = null;

                throw new ConnectionException("Failed to connect to {$this->config['ip']}:{$this->config['port']}: {$errorMsg}");
            }
        }

        // Wait for connection to complete
        $write = [$this->socket];
        $read = $except = [];
        $timeout = (int) ($this->config['timeout'] / 1000);

        $ready = socket_select($read, $write, $except, $timeout);

        if ($ready === false) {
            socket_close($this->socket);
            $this->socket = null;

            throw new ConnectionException('Connection failed: ' . socket_strerror(socket_last_error()));
        }

        if ($ready === 0) {
            socket_close($this->socket);
            $this->socket = null;

            throw new ConnectionException("Connection timeout to {$this->config['ip']}:{$this->config['port']}");
        }

        // Check if connection was successful
        $error = socket_get_option($this->socket, SOL_SOCKET, SO_ERROR);
        if ($error !== 0) {
            socket_close($this->socket);
            $this->socket = null;

            throw new ConnectionException('Connection failed: ' . socket_strerror($error));
        }

        // Set blocking mode and timeouts
        socket_set_block($this->socket);
        $timeout = ['sec' => (int) ($this->config['timeout'] / 1000), 'usec' => 0];
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);

        return true;
    }

    public function disconnect(): void
    {
        if ($this->socket !== null) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    public function send(Packet $packet): Response
    {
        if (!$this->isConnected()) {
            throw new ConnectionException('Not connected');
        }

        // Build the packet data directly for network protocol
        $packetData = $packet->build();

        // Build network packet according to documented format
        $networkPacket = '';

        // 1. ID Code (4 bytes, high byte first)
        $networkPacket .= pack('N', $this->config['networkIdCode']);

        // 2. Calculate network data length (from "Packet type" to "Packet data checksum")
        // For basic protocol: packetType(1) + cardType(1) + cardId(1) + cmd(1) + additional(1) + data + checksum(2)
        // For external calls (0x7B): same structure but with external calls data format
        $networkDataLength = \strlen($packetData) + 2; // +2 for checksum
        $networkPacket .= pack('v', $networkDataLength); // FIXED: Use little-endian 'v' instead of big-endian 'n'

        // 3. Reservation (2 bytes)
        $networkPacket .= pack('v', 0); // FIXED: Use little-endian 'v' instead of big-endian 'n'

        // 4. Add the packet data (which includes packet type, card type, etc.)
        $networkPacket .= $packetData;

        // 5. Calculate and add checksum (2 bytes, low byte first)
        $checksum = 0;
        for ($i = 0; $i < \strlen($packetData); $i++) {
            $checksum += \ord($packetData[$i]);
            $checksum &= 0xFFFF;
        }
        $networkPacket .= pack('v', $checksum);

        // Send data
        $sent = @socket_write($this->socket, $networkPacket, \strlen($networkPacket));

        if ($sent === false) {
            throw new CommunicationException('Failed to send data: ' . socket_strerror(socket_last_error($this->socket)));
        }

        // Read response
        $responseData = $this->readResponse();

        return Response::parse($responseData);
    }

    public function isConnected(): bool
    {
        return $this->socket !== null;
    }

    private function readResponse(): string
    {
        // Read network response header (8 bytes: ID + length + reserved)
        $networkHeader = '';
        $networkHeaderLength = 8;

        while (\strlen($networkHeader) < $networkHeaderLength) {
            $chunk = @socket_read($this->socket, $networkHeaderLength - \strlen($networkHeader));

            if ($chunk === false) {
                throw new CommunicationException('Failed to read response header: ' . socket_strerror(socket_last_error($this->socket)));
            }

            if ($chunk === '') {
                throw new TimeoutException('Timeout reading response header');
            }

            $networkHeader .= $chunk;
        }

        // Parse network header
        $idCode = unpack('N', substr($networkHeader, 0, 4))[1];
        $dataLength = unpack('v', substr($networkHeader, 4, 2))[1]; // FIXED: Use little-endian 'v' to match send format
        // Skip reserved bytes at positions 6-7

        // Read the network data (packet type to checksum)
        $networkData = '';

        while (\strlen($networkData) < $dataLength) {
            $chunk = @socket_read($this->socket, $dataLength - \strlen($networkData));

            if ($chunk === false) {
                throw new CommunicationException('Failed to read response data: ' . socket_strerror(socket_last_error($this->socket)));
            }

            if ($chunk === '') {
                throw new TimeoutException('Timeout reading response data');
            }

            $networkData .= $chunk;
        }

        // For network protocol, the response data already contains the packet structure
        // Just need to extract the core data and reformat for Response::parse()
        if (\strlen($networkData) < 7) {
            throw new CommunicationException('Invalid network response data length');
        }

        // Extract checksum (last 2 bytes)
        $checksumPos = \strlen($networkData) - 2;
        $actualData = substr($networkData, 0, $checksumPos);
        $receivedChecksum = substr($networkData, $checksumPos, 2);

        // Verify checksum
        $calculatedChecksum = 0;
        for ($i = 0; $i < \strlen($actualData); $i++) {
            $calculatedChecksum += \ord($actualData[$i]);
            $calculatedChecksum &= 0xFFFF;
        }

        // Try both endianness formats for checksum since controller might use big-endian
        $checksumLittleEndian = unpack('v', $receivedChecksum)[1];
        $checksumBigEndian = unpack('n', $receivedChecksum)[1];

        if ($calculatedChecksum !== $checksumLittleEndian && $calculatedChecksum !== $checksumBigEndian) {
            throw new CommunicationException(
                \sprintf(
                    'Checksum mismatch: calculated %04X, received %04X (LE) / %04X (BE)',
                    $calculatedChecksum,
                    $checksumLittleEndian,
                    $checksumBigEndian,
                ),
            );
        }

        // For network protocol, the response data doesn't contain its own checksum
        // We need to add a dummy checksum for Response::parse to work correctly
        $dummyChecksum = $this->calculateChecksum($actualData);

        return $actualData . pack('v', $dummyChecksum);
    }

    /**
     * Calculate checksum for data.
     */
    private function calculateChecksum(string $data): int
    {
        $checksum = 0;
        for ($i = 0; $i < \strlen($data); $i++) {
            $checksum += \ord($data[$i]);
            $checksum &= 0xFFFF;
        }

        return $checksum;
    }
}
