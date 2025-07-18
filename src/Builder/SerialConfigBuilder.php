<?php

declare(strict_types=1);

namespace LEDController\Builder;

use LEDController\Enum\BaudRate;
use LEDController\Manager\SetupManager;

/**
 * Serial configuration builder with modern enum support.
 */
class SerialConfigBuilder
{
    private readonly SetupManager $manager;

    private int $controllerId = 1;

    private BaudRate $baudRate = BaudRate::BAUD_115200;

    public function __construct(SetupManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set controller ID.
     */
    public function controllerId(int $id): self
    {
        if ($id < 1 || $id > 255) {
            throw new \InvalidArgumentException("Controller ID must be between 1 and 255, got: {$id}");
        }

        $this->controllerId = $id;

        return $this;
    }

    /**
     * Set baud rate using enum.
     */
    public function baudRate(BaudRate $rate): self
    {
        $this->baudRate = $rate;

        return $this;
    }

    /**
     * Set baud rate using integer (legacy support).
     */
    public function baudRateInt(int $rate): self
    {
        $this->baudRate = match ($rate) {
            9600 => BaudRate::BAUD_9600,
            19200 => BaudRate::BAUD_19200,
            38400 => BaudRate::BAUD_38400,
            57600 => BaudRate::BAUD_57600,
            115200 => BaudRate::BAUD_115200,
            default => throw new \InvalidArgumentException("Unsupported baud rate: {$rate}")
        };

        return $this;
    }

    /**
     * Set baud rate to 115200 (fastest).
     */
    public function fastest(): self
    {
        $this->baudRate = BaudRate::BAUD_115200;

        return $this;
    }

    /**
     * Set baud rate to 9600 (slowest).
     */
    public function slowest(): self
    {
        $this->baudRate = BaudRate::BAUD_9600;

        return $this;
    }

    /**
     * Set baud rate to 115200.
     */
    public function baud115200(): self
    {
        $this->baudRate = BaudRate::BAUD_115200;

        return $this;
    }

    /**
     * Set baud rate to 57600.
     */
    public function baud57600(): self
    {
        $this->baudRate = BaudRate::BAUD_57600;

        return $this;
    }

    /**
     * Set baud rate to 38400.
     */
    public function baud38400(): self
    {
        $this->baudRate = BaudRate::BAUD_38400;

        return $this;
    }

    /**
     * Set baud rate to 19200.
     */
    public function baud19200(): self
    {
        $this->baudRate = BaudRate::BAUD_19200;

        return $this;
    }

    /**
     * Set baud rate to 9600.
     */
    public function baud9600(): self
    {
        $this->baudRate = BaudRate::BAUD_9600;

        return $this;
    }

    /**
     * Apply configuration.
     */
    public function apply(): SetupManager
    {
        $this->manager->setSerialConfig($this->controllerId, $this->baudRate->value);

        return $this->manager;
    }

    /**
     * Get built configuration.
     *
     * @return array<string, int|BaudRate|string> Serial configuration values
     */
    public function getConfig(): array
    {
        return [
            'controllerId' => $this->controllerId,
            'baudRateEnum' => $this->baudRate,
            'baudRateCode' => $this->baudRate->value,
            'baudRate' => $this->baudRate->getRate(),
            'baudRateName' => $this->baudRate->getName(),
        ];
    }

    /**
     * Validate current configuration.
     */
    public function validate(): bool
    {
        if ($this->controllerId < 1 || $this->controllerId > 255) {
            throw new \InvalidArgumentException('Controller ID must be between 1 and 255');
        }

        return true;
    }

    /**
     * Reset to default configuration.
     */
    public function reset(): self
    {
        $this->controllerId = 1;
        $this->baudRate = BaudRate::BAUD_115200;

        return $this;
    }

    /**
     * Get current baud rate enum.
     */
    public function getCurrentBaudRate(): BaudRate
    {
        return $this->baudRate;
    }

    /**
     * Get current controller ID.
     */
    public function getCurrentControllerId(): int
    {
        return $this->controllerId;
    }
}
