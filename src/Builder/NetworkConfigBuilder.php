<?php

declare(strict_types=1);

namespace LEDController\Builder;

use LEDController\Manager\SetupManager;

/**
 * Network configuration builder with readonly properties.
 */
class NetworkConfigBuilder
{
    private readonly SetupManager $manager;

    /**
     * @var array<string, int|string> Network configuration values
     */
    private array $config = [];

    public function __construct(SetupManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set IP address.
     */
    public function ip(string $ip): self
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid IP address format: {$ip}");
        }

        $this->config['ip'] = $ip;

        return $this;
    }

    /**
     * Set gateway.
     */
    public function gateway(string $gateway): self
    {
        if (!filter_var($gateway, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid gateway IP address format: {$gateway}");
        }

        $this->config['gateway'] = $gateway;

        return $this;
    }

    /**
     * Set subnet mask.
     */
    public function subnet(string $subnet): self
    {
        if (!filter_var($subnet, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid subnet mask format: {$subnet}");
        }

        $this->config['subnet'] = $subnet;

        return $this;
    }

    /**
     * Set port.
     */
    public function port(int $port): self
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException("Port must be between 1 and 65535, got: {$port}");
        }

        $this->config['port'] = $port;

        return $this;
    }

    /**
     * Set network ID.
     */
    public function networkId(int $networkId): self
    {
        $this->config['networkId'] = $networkId;

        return $this;
    }

    /**
     * Set common home network settings (192.168.1.x).
     */
    public function homeNetwork(string $ip = '192.168.1.222'): self
    {
        return $this->ip($ip)
            ->gateway('192.168.1.1')
            ->subnet('255.255.255.0');
    }

    /**
     * Set common office network settings (192.168.1.x).
     */
    public function officeNetwork(string $ip = '192.168.1.222'): self
    {
        return $this->ip($ip)
            ->gateway('192.168.1.1')
            ->subnet('255.255.255.0');
    }

    /**
     * Apply configuration.
     */
    public function apply(): SetupManager
    {
        // Fill in defaults
        if (!isset($this->config['gateway']) && isset($this->config['ip'])) {
            $this->config['gateway'] = $this->getDefaultGateway($this->config['ip']);
        }

        if (!isset($this->config['subnet'])) {
            $this->config['subnet'] = '255.255.255.0';
        }

        if (!isset($this->config['port'])) {
            $this->config['port'] = 5200;
        }

        if (!isset($this->config['networkId'])) {
            $this->config['networkId'] = 0xFFFFFFFF;
        }

        $this->manager->setNetworkConfig($this->config);

        return $this->manager;
    }

    /**
     * Get built configuration.
     *
     * @return array<string, int|string> Network configuration values
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Validate current configuration.
     */
    public function validate(): bool
    {
        if (!isset($this->config['ip'])) {
            throw new \InvalidArgumentException('IP address is required');
        }

        return true;
    }

    /**
     * Reset configuration to defaults.
     */
    public function reset(): self
    {
        $this->config = [];

        return $this;
    }

    /**
     * Get default gateway for IP.
     */
    private function getDefaultGateway(string $ip): string
    {
        $parts = explode('.', $ip);
        if (\count($parts) !== 4) {
            throw new \InvalidArgumentException("Invalid IP address format: {$ip}");
        }

        $parts[3] = '1';

        return implode('.', $parts);
    }
}
