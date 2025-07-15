<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\Packet;
use LEDController\Exception\ExternalCallsException;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\Command;

/**
 * External Calls Manager for protocol command 0x7B
 */
class ExternalCallsManager
{
    private LEDController $controller;
    private array $variables = [];
    private array $timers = [];
    private bool $splitScreenMode = false;
    private array $currentWindows = [];

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Split screen into multiple windows
     */
    public function splitScreen(array $windows): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x01); // Split screen

        $data = chr(count($windows));

        foreach ($windows as $window) {
            $this->validateWindow($window);
            $data .= pack('n', $window['x']); // Big-endian
            $data .= pack('n', $window['y']);
            $data .= pack('n', $window['width']);
            $data .= pack('n', $window['height']);
        }

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ExternalCallsException("Failed to split screen: " . $response->getReturnCodeMessage());
        }

        $this->splitScreenMode = true;
        $this->currentWindows = $windows;

        return $this;
    }

    /**
     * Display text in a window - delegates to main controller method
     *
     * This method provides a convenient way to display text in specific windows
     * for split screen scenarios. It delegates to the main displayText() method
     * which is the single source of truth for text display functionality.
     */
    public function displayText(int $windowId, string $text, array $options = []): self
    {
        // Merge windowId into options and delegate to the main controller method
        $options['window'] = $windowId;
        $this->controller->displayText($text, $options);

        return $this;
    }

    /**
     * Display image in a window
     */
    public function displayImage(int $windowId, string $imageData, array $options = []): self
    {
        $this->validateWindowId($windowId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x03); // Image display

        $data = chr($windowId);
        $data .= chr($this->convertEffectToInt($options['effect'] ?? Effect::DRAW));
        $data .= chr($options['speed'] ?? 5);
        $data .= pack('n', $options['stay'] ?? 10); // Big-endian
        $data .= pack('n', $options['x'] ?? 0); // Big-endian
        $data .= pack('n', $options['y'] ?? 0); // Big-endian

        // Image data
        $data .= pack('N', strlen($imageData)); // Big-endian
        $data .= $imageData;

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ExternalCallsException("Failed to display image: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Exit split screen
     */
    public function exitSplitScreen(): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x07); // Exit split screen

        $packet->setData('');

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ExternalCallsException("Failed to exit split screen: " . $response->getReturnCodeMessage());
        }

        $this->splitScreenMode = false;
        $this->currentWindows = [];

        return $this;
    }

    /**
     * Play a program
     */
    public function playProgram(int $programId): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x08); // Play program

        $data = chr($programId);
        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ExternalCallsException("Failed to play program: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Set a variable
     */
    public function setVariable(int $variableId, string $value): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::EXTERNAL_CALLS->value);
        $packet->setSubCommand(0x10); // Set variable

        $data = chr($variableId);
        $data .= pack('n', strlen($value)); // Big-endian
        $data .= $value;

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new ExternalCallsException("Failed to set variable: " . $response->getReturnCodeMessage());
        }

        $this->variables[$variableId] = $value;

        return $this;
    }

    /**
     * Get current state
     */
    public function getState(): array
    {
        return [
            'splitScreenMode' => $this->splitScreenMode,
            'currentWindows' => $this->currentWindows,
            'variables' => $this->variables,
            'timers' => $this->timers
        ];
    }

    /**
     * Validate window structure
     */
    private function validateWindow(array $window): void
    {
        if (!isset($window['x']) || !isset($window['y']) || !isset($window['width']) || !isset($window['height'])) {
            throw new ExternalCallsException("Window must have x, y, width, and height");
        }
    }

    /**
     * Validate window ID
     */
    private function validateWindowId(int $windowId): void
    {
        if ($windowId < 0 || $windowId > 15) {
            throw new ExternalCallsException("Window ID must be between 0 and 15");
        }
    }

    /**
     * Convert effect value to integer
     */
    private function convertEffectToInt(mixed $effect): int
    {
        if ($effect instanceof Effect) {
            return $effect->value;
        }

        return (int) $effect;
    }

    /**
     * Convert alignment value to integer
     */
    private function convertAlignToInt(mixed $align): int
    {
        if ($align instanceof Alignment) {
            return $align->value;
        }

        return (int) $align;
    }
}
