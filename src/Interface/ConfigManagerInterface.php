<?php

declare(strict_types=1);

namespace LEDController\Interface;

/**
 * Interface for configuration management operations.
 */
interface ConfigManagerInterface extends ManagerInterface
{
    /**
     * Get configuration value.
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     *
     * @return mixed Configuration value
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set configuration value.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self;

    /**
     * Check if configuration key exists.
     */
    public function has(string $key): bool;

    /**
     * Remove configuration key.
     */
    public function remove(string $key): self;

    /**
     * Load configuration from file.
     */
    public function loadFromFile(string $filePath): self;

    /**
     * Save configuration to file.
     */
    public function saveToFile(string $filePath = ''): self;

    /**
     * Convert configuration to array.
     *
     * @return array<string, mixed> Configuration array
     */
    public function toArray(): array;

    /**
     * Reset configuration to defaults.
     */
    public function reset(): self;

    /**
     * Merge configuration with existing config.
     *
     * @param array<string, mixed> $config Configuration to merge
     */
    public function merge(array $config): self;
}
