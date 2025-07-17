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
     * Get display dimensions from controller configuration
     * 
     * @return array Display dimensions ['width' => int, 'height' => int]
     */
    public function getDisplayDimensions(): array
    {
        $config = $this->controller->getConfig();
        
        // Try to get from controller config first
        if (isset($config['displayWidth']) && isset($config['displayHeight'])) {
            return [
                'width' => $config['displayWidth'],
                'height' => $config['displayHeight']
            ];
        }
        
        // Try to get from display config
        if (isset($config['display']['defaultWidth']) && isset($config['display']['defaultHeight'])) {
            return [
                'width' => $config['display']['defaultWidth'],
                'height' => $config['display']['defaultHeight']
            ];
        }
        
        // Fallback to common default dimensions
        return [
            'width' => 128,
            'height' => 32
        ];
    }

    /**
     * Create a table layout with equally divided windows
     * 
     * @param int $columns Number of columns in the table
     * @param int $rows Number of rows in the table
     * @param array $displayDimensions Optional display dimensions ['width' => int, 'height' => int]
     * @return array Array of window coordinates for splitScreen
     * @throws ExternalCallsException If invalid parameters or too many windows
     */
    public function createTableLayout(int $columns, int $rows, ?array $displayDimensions = null): array
    {
        // Validate input parameters
        if ($columns < 1 || $columns > 8) {
            throw new ExternalCallsException("Columns must be between 1 and 8");
        }
        
        if ($rows < 1 || $rows > 8) {
            throw new ExternalCallsException("Rows must be between 1 and 8");
        }
        
        $totalWindows = $columns * $rows;
        if ($totalWindows > 8) {
            throw new ExternalCallsException("Total windows ($totalWindows) cannot exceed 8");
        }
        
        // Get display dimensions (use controller config if not provided)
        if ($displayDimensions === null) {
            $displayDimensions = $this->getDisplayDimensions();
        }
        
        $displayWidth = $displayDimensions['width'] ?? 128;
        $displayHeight = $displayDimensions['height'] ?? 32;
        
        // Calculate window dimensions
        $windowWidth = (int)($displayWidth / $columns);
        $windowHeight = (int)($displayHeight / $rows);
        
        // Ensure minimum window size
        if ($windowWidth < 8 || $windowHeight < 8) {
            throw new ExternalCallsException("Window size too small: {$windowWidth}x{$windowHeight} pixels. Minimum is 8x8.");
        }
        
        $windows = [];
        $windowId = 0;
        
        // Create windows for each cell in the table
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $columns; $col++) {
                $x = $col * $windowWidth;
                $y = $row * $windowHeight;
                
                // Adjust width/height for last column/row to use remaining space
                $actualWidth = ($col === $columns - 1) ? ($displayWidth - $x) : $windowWidth;
                $actualHeight = ($row === $rows - 1) ? ($displayHeight - $y) : $windowHeight;
                
                $windows[] = [
                    'id' => $windowId,
                    'x' => $x,
                    'y' => $y,
                    'width' => $actualWidth,
                    'height' => $actualHeight
                ];
                
                $windowId++;
            }
        }
        
        return $windows;
    }

    /**
     * Create and apply a table layout with equally divided windows
     * 
     * @param int $columns Number of columns in the table
     * @param int $rows Number of rows in the table
     * @param array $displayDimensions Optional display dimensions ['width' => int, 'height' => int]
     * @return array Array of window coordinates that were applied
     * @throws ExternalCallsException If invalid parameters or split screen fails
     */
    public function applyTableLayout(int $columns, int $rows, ?array $displayDimensions = null): array
    {
        $windows = $this->createTableLayout($columns, $rows, $displayDimensions);
        
        // Apply the split screen layout
        $this->splitScreen($windows);
        
        return $windows;
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
