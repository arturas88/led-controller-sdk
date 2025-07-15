<?php

namespace LEDController\Interface;

use LEDController\LEDController;

/**
 * Base interface for all manager classes
 */
interface ManagerInterface
{
    /**
     * Constructor must accept LEDController instance
     */
    public function __construct(LEDController $controller);

    /**
     * Initialize the manager (if needed)
     */
    public function initialize(): void;

    /**
     * Get the controller instance
     */
    public function getController(): LEDController;

    /**
     * Check if manager is ready for operations
     */
    public function isReady(): bool;

    /**
     * Clean up resources
     */
    public function cleanup(): void;
}
