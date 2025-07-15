<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\Interface\ManagerInterface;
use LEDController\Exception\ConfigException;

/**
 * Abstract base class for all managers
 */
abstract class AbstractManager implements ManagerInterface
{
    protected LEDController $controller;
    protected bool $initialized = false;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize the manager
     */
    public function initialize(): void
    {
        $this->initialized = true;
        $this->doInitialize();
    }

    /**
     * Get the controller instance
     */
    public function getController(): LEDController
    {
        return $this->controller;
    }

    /**
     * Check if manager is ready for operations
     */
    public function isReady(): bool
    {
        return $this->initialized && $this->controller->isConnected();
    }

    /**
     * Clean up resources
     */
    public function cleanup(): void
    {
        $this->doCleanup();
        $this->initialized = false;
    }

    /**
     * Check if manager is ready and throw exception if not
     */
    protected function ensureReady(): void
    {
        if (!$this->isReady()) {
            throw new ConfigException(get_class($this) . " is not ready for operations");
        }
    }

    /**
     * Get card ID from controller config
     */
    protected function getCardId(): int
    {
        return $this->controller->getConfig()['cardId'];
    }

    /**
     * Override this method to perform manager-specific initialization
     */
    protected function doInitialize(): void
    {
        // Default implementation - override in subclasses if needed
    }

    /**
     * Override this method to perform manager-specific cleanup
     */
    protected function doCleanup(): void
    {
        // Default implementation - override in subclasses if needed
    }
}
