<?php

declare(strict_types=1);

namespace LEDController\Manager;

use LEDController\Enum\Command;
use LEDController\Exception\SetupException;
use LEDController\LEDController;
use LEDController\Packet;

/**
 * Setup Manager for network and serial configuration.
 */
class SetupManager
{
    private LEDController $controller;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Query network configuration.
     *
     * @return array<string, mixed> Network configuration array
     */
    public function queryNetworkConfig(): array
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::NETWORK_SETUP->value);
        $packet->setData(\chr(0x01)); // Query command

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new SetupException('Failed to query network config: ' . $response->getReturnCodeMessage());
        }

        return $this->parseNetworkConfig($response->getData());
    }

    /**
     * Set network configuration.
     *
     * @param array<string, mixed> $config Network configuration array
     */
    public function setNetworkConfig(array $config): self
    {
        $this->validateNetworkConfig($config);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::NETWORK_SETUP->value);

        $data = \chr(0x00); // Set command

        // IP Address (4 bytes)
        $ipBytes = $this->ipToBytes($config['ip']);
        $data .= pack('C4', ...$ipBytes);

        // Gateway (4 bytes)
        $gatewayBytes = $this->ipToBytes($config['gateway']);
        $data .= pack('C4', ...$gatewayBytes);

        // Subnet mask (4 bytes)
        $subnetBytes = $this->ipToBytes($config['subnet']);
        $data .= pack('C4', ...$subnetBytes);

        // Port (2 bytes)
        $data .= pack('n', $config['port']); // Big-endian

        // Network ID (4 bytes)
        $data .= pack('N', $config['networkId']); // Big-endian

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new SetupException('Failed to set network config: ' . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Query serial configuration.
     *
     * @return array<string, int> Serial configuration array
     */
    public function querySerialConfig(): array
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::ID_BAUD_SETUP->value);

        $data = \chr(0x01); // Query command
        $data .= \chr(0x00); // Reserved
        $data .= \chr(0x00); // Reserved

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new SetupException('Failed to query serial config: ' . $response->getReturnCodeMessage());
        }

        return $this->parseSerialConfig($response->getData());
    }

    /**
     * Set controller ID and baud rate.
     */
    public function setSerialConfig(int $controllerId, int $baudRate): self
    {
        if ($controllerId < 1 || $controllerId > 254) {
            throw new SetupException('Controller ID must be between 1 and 254');
        }

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::ID_BAUD_SETUP->value);

        $data = \chr(0x00); // Set command
        $data .= \chr($controllerId);
        $data .= \chr($baudRate);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new SetupException('Failed to set serial config: ' . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Quick network setup.
     */
    public function quickNetworkSetup(string $ip, ?string $gateway = null, ?string $subnet = null, int $port = 5200): self
    {
        $gateway ??= $this->getDefaultGateway($ip);
        $subnet ??= '255.255.255.0';

        $config = [
            'ip' => $ip,
            'gateway' => $gateway,
            'subnet' => $subnet,
            'port' => $port,
            'networkId' => 0xFFFFFFFF,
        ];

        return $this->setNetworkConfig($config);
    }

    /**
     * Parse network configuration response.
     *
     * @param string $data Raw response data
     *
     * @return array<string, mixed> Parsed network configuration
     */
    private function parseNetworkConfig(string $data): array
    {
        if (\strlen($data) < 18) {
            throw new SetupException('Invalid network configuration response');
        }

        $ip = implode('.', unpack('C4', substr($data, 1, 4)));
        $gateway = implode('.', unpack('C4', substr($data, 5, 4)));
        $subnet = implode('.', unpack('C4', substr($data, 9, 4)));
        $port = unpack('n', substr($data, 13, 2))[1];
        $networkId = unpack('N', substr($data, 15, 4))[1];

        return [
            'ip' => $ip,
            'gateway' => $gateway,
            'subnet' => $subnet,
            'port' => $port,
            'networkId' => $networkId,
        ];
    }

    /**
     * Parse serial configuration response.
     *
     * @param string $data Raw response data
     *
     * @return array<string, int> Parsed serial configuration
     */
    private function parseSerialConfig(string $data): array
    {
        if (\strlen($data) < 3) {
            throw new SetupException('Invalid serial configuration response');
        }

        $controllerId = \ord($data[1]);
        $baudRate = \ord($data[2]);

        return [
            'controllerId' => $controllerId,
            'baudRate' => $baudRate,
        ];
    }

    /**
     * Validate network configuration.
     *
     * @param array<string, mixed> $config Network configuration to validate
     */
    private function validateNetworkConfig(array $config): void
    {
        if (!isset($config['ip']) || !filter_var($config['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new SetupException('Invalid IP address');
        }

        if (!isset($config['gateway']) || !filter_var($config['gateway'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new SetupException('Invalid gateway address');
        }

        if (!isset($config['subnet']) || !filter_var($config['subnet'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new SetupException('Invalid subnet mask');
        }

        if (!isset($config['port']) || $config['port'] < 1 || $config['port'] > 65535) {
            throw new SetupException('Invalid port number');
        }

        if (!isset($config['networkId']) || $config['networkId'] < 0 || $config['networkId'] > 0xFFFFFFFF) {
            throw new SetupException('Invalid network ID');
        }
    }

    /**
     * Convert IP address to bytes.
     *
     * @param string $ip IP address to convert
     *
     * @return array<int, int> Array of 4 bytes representing the IP address
     */
    private function ipToBytes(string $ip): array
    {
        $parts = explode('.', $ip);

        if (\count($parts) !== 4) {
            throw new SetupException('Invalid IP address format');
        }

        return array_map('intval', $parts);
    }

    /**
     * Get default gateway for IP.
     */
    private function getDefaultGateway(string $ip): string
    {
        $parts = explode('.', $ip);
        $parts[3] = '1';

        return implode('.', $parts);
    }
}
