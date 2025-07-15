<?php

namespace LEDController\Manager;

use LEDController\Enum\Protocol;
use LEDController\Exception\ConfigException;

/**
 * Configuration manager for LED controller settings
 */
class ConfigManager
{
    private array $config = [];
    private array $defaults = [];
    private string $configFile = '';

    /**
     * Default configuration values
     */
    private const DEFAULT_CONFIG = [
        'network' => [
            'ip' => '192.168.10.61',
            'port' => 5200,
            'gateway' => '192.168.10.1',
            'subnet' => '255.255.255.0',
            'networkId' => 0xFFFFFFFF,
            'timeout' => 5000,
            'retries' => 3,
        ],
        'serial' => [
            'port' => 'COM1',
            'baudRate' => 115200,
            'dataBits' => 8,
            'parity' => 'none',
            'stopBits' => 1,
            'flowControl' => 'none',
        ],
        'controller' => [
            'cardId' => 1,
            'controllerType' => 'C-Power5200',
            'firmwareVersion' => '1.0',
            'maxWindows' => 8,
            'maxVariables' => 100,
            'maxTimers' => 7,
            'maxZones' => 8,
        ],
        'display' => [
            'defaultWidth' => 128,
            'defaultHeight' => 32,
            'colorMode' => Protocol::COLOR_FULL,
            'maxBrightness' => 31,
            'minBrightness' => 0,
        ],
        'communication' => [
            'type' => 'network', // 'network' or 'serial'
            'autoReconnect' => true,
            'keepalive' => true,
            'compression' => false,
            'encryption' => false,
        ],
        'files' => [
            'uploadChunkSize' => 512,
            'downloadChunkSize' => 512,
            'maxFileSize' => 10485760, // 10MB
            'allowedExtensions' => ['.lpp', '.bmp', '.jpg', '.gif', '.png', '.txt'],
        ],
        'logging' => [
            'enabled' => true,
            'level' => 'info',
            'file' => null,
            'maxSize' => 10485760, // 10MB
            'rotate' => true,
        ],
    ];

    public function __construct(?string $configFile = null)
    {
        $this->configFile = $configFile ?? $this->getDefaultConfigPath();
        $this->defaults = self::DEFAULT_CONFIG;

        $this->loadConfiguration();
    }

    /**
     * Get default configuration file path
     */
    private function getDefaultConfigPath(): string
    {
        return dirname(__DIR__, 2) . '/config/ledcontroller.json';
    }

    /**
     * Load configuration from file
     */
    public function loadConfiguration(): void
    {
        if (file_exists($this->configFile)) {
            $configData = json_decode(file_get_contents($this->configFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConfigException("Invalid JSON in configuration file: " . json_last_error_msg());
            }

            $this->config = array_merge($this->defaults, $configData);
        } else {
            $this->config = $this->defaults;
        }
    }

    /**
     * Save configuration to file
     */
    public function saveConfiguration(): void
    {
        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            if (!mkdir($configDir, 0755, true)) {
                throw new ConfigException("Failed to create configuration directory: $configDir");
            }
        }

        $jsonData = json_encode($this->config, JSON_PRETTY_PRINT);

        if (file_put_contents($this->configFile, $jsonData) === false) {
            throw new ConfigException("Failed to save configuration to file: {$this->configFile}");
        }
    }

    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->getNestedValue($this->config, $key, $default);
    }

    /**
     * Set configuration value
     */
    public function set(string $key, $value): void
    {
        $this->setNestedValue($this->config, $key, $value);
    }

    /**
     * Get all configuration
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Reset to defaults
     */
    public function reset(): void
    {
        $this->config = $this->defaults;
    }

    /**
     * Get network configuration
     */
    public function getNetworkConfig(): array
    {
        return $this->config['network'];
    }

    /**
     * Get serial configuration
     */
    public function getSerialConfig(): array
    {
        return $this->config['serial'];
    }

    /**
     * Get controller configuration
     */
    public function getControllerConfig(): array
    {
        return $this->config['controller'];
    }

    /**
     * Get display configuration
     */
    public function getDisplayConfig(): array
    {
        return $this->config['display'];
    }

    /**
     * Get communication configuration
     */
    public function getCommunicationConfig(): array
    {
        return $this->config['communication'];
    }

    /**
     * Get nested value from array
     */
    private function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (is_array($array) && isset($array[$k])) {
                $array = $array[$k];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Set nested value in array
     */
    private function setNestedValue(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!is_array($current)) {
                $current = [];
            }

            if (!isset($current[$k])) {
                $current[$k] = [];
            }

            $current = &$current[$k];
        }

        $current = $value;
    }
}
