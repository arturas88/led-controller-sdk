<?php

namespace LEDController;

/**
 * Controller status class
 */
class ControllerStatus
{
    private array $versionInfo = [];
    private ?float $temperature = null;
    private ?int $humidity = null;
    private int $freeSpace = 0;
    private array $brightnessValues = [];

    public function setVersionInfo(array $info): void
    {
        $this->versionInfo = $info;
    }

    public function setTemperature(array $tempData): void
    {
        $this->temperature = $tempData['celsius'] ?? null;
        $this->humidity = $tempData['humidity'] ?? null;
    }

    public function setFreeSpace(int $bytes): void
    {
        $this->freeSpace = $bytes;
    }

    public function setBrightnessValues(array $values): void
    {
        $this->brightnessValues = $values;
    }

    public function getCardType(): ?int
    {
        return $this->versionInfo['cardType'] ?? null;
    }

    public function getLogicVersion(): ?string
    {
        return $this->versionInfo['logicVersion'] ?? null;
    }

    public function getBiosVersion(): ?string
    {
        return $this->versionInfo['biosVersion'] ?? null;
    }

    public function getAppVersion(): ?string
    {
        return $this->versionInfo['appVersion'] ?? null;
    }

    public function getFirmwareVersion(): ?string
    {
        return $this->getAppVersion();
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function getHumidity(): ?int
    {
        return $this->humidity;
    }

    public function getFreeSpace(): int
    {
        return $this->freeSpace;
    }

    public function getFreeSpaceMB(): float
    {
        return round($this->freeSpace / 1024 / 1024, 2);
    }

    public function getBrightnessValues(): array
    {
        return $this->brightnessValues;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->versionInfo,
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
            'freeSpace' => $this->freeSpace,
            'freeSpaceMB' => $this->getFreeSpaceMB(),
            'brightness' => $this->brightnessValues,
        ];
    }
}
