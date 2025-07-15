<?php

namespace LEDController\Interface;

/**
 * Interface for configuration management operations
 */
interface ConfigManagerInterface extends ManagerInterface
{
    /**
     * Get configuration value
     */
    public function get(string $key, $default = null);

    /**
     * Set configuration value
     */
    public function set(string $key, $value): self;

    /**
     * Check if configuration key exists
     */
    public function has(string $key): bool;

    /**
     * Remove configuration key
     */
    public function remove(string $key): self;

    /**
     * Load configuration from file
     */
    public function loadFromFile(string $filePath): self;

    /**
     * Save configuration to file
     */
    public function saveToFile(string $filePath = ''): self;

    /**
     * Get all configuration as array
     */
    public function toArray(): array;

    /**
     * Reset configuration to defaults
     */
    public function reset(): self;

    /**
     * Merge configuration with another array
     */
    public function merge(array $config): self;
}
