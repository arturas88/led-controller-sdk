<?php

declare(strict_types=1);

namespace LEDController;

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;
use LEDController\Exception\FileNotFoundException;
use LEDController\Exception\ValidationException;

/**
 * Program builder for creating LED display programs.
 */
class ProgramBuilder
{
    private LEDController $controller;

    /**
     * @var array<int, array<string, mixed>> Window configurations
     */
    private array $windows = [];

    /**
     * @var array<int, array<string, mixed>> Window content data
     */
    private array $windowContent = [];

    private int $currentWindow = 0;

    private int $currentContent = 0;

    /**
     * @var array<string, mixed> Program properties
     */
    private array $properties = [];

    private int $screenWidth;

    private int $screenHeight;

    private int $colorMode;

    /**
     * @var array<int, mixed> Program data
     */
    private array $program = [];

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;

        // Default screen size and color
        $this->screenWidth = 128;
        $this->screenHeight = 32;
        $this->colorMode = Protocol::COLOR_FULL;
    }

    /**
     * Create a new program.
     */
    public function create(int $width, int $height, int $colorMode = Protocol::COLOR_FULL): self
    {
        $this->screenWidth = $width;
        $this->screenHeight = $height;
        $this->colorMode = $colorMode;
        $this->windows = [];
        $this->windowContent = [];
        $this->properties = [];

        return $this;
    }

    /**
     * Set repeat times for the program.
     */
    public function setRepeatTimes(int $times): self
    {
        if ($times < 1 || $times > 65535) {
            throw new ValidationException('Repeat times must be between 1 and 65535');
        }

        $this->properties['repeatTimes'] = $times;
        unset($this->properties['playTime']); // Only one can be set

        return $this;
    }

    /**
     * Set play time for the program.
     */
    public function setPlayTime(int $seconds): self
    {
        if ($seconds < 1 || $seconds > 65535) {
            throw new ValidationException('Play time must be between 1 and 65535 seconds');
        }

        $this->properties['playTime'] = $seconds;
        unset($this->properties['repeatTimes']); // Only one can be set

        return $this;
    }

    /**
     * Add a window to the program.
     */
    public function addWindow(int $x, int $y, int $width, int $height, ?int $windowNo = null): self
    {
        // Validate window position
        if ($x < 0 || $y < 0 || $x + $width > $this->screenWidth || $y + $height > $this->screenHeight) {
            throw new ValidationException('Window position/size is outside screen boundaries');
        }

        if ($windowNo === null) {
            $windowNo = \count($this->windows);
        }

        if ($windowNo < 0 || $windowNo > 7) {
            throw new ValidationException('Window number must be between 0 and 7');
        }

        $this->windows[$windowNo] = [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];

        return $this;
    }

    /**
     * Add text to a window.
     *
     * @param array<string, mixed> $options Text display options
     */
    public function addText(int $windowNo, string $text, array $options = []): self
    {
        $this->validateWindow($windowNo);

        $defaultOptions = [
            'font' => FontSize::FONT_16,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'speed' => 5,
            'stay' => 10,
            'align' => Alignment::LEFT,
        ];

        $options = array_merge($defaultOptions, $options);

        $this->windowContent[$windowNo][] = [
            'type' => 'text',
            'content' => $text,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Add image to a window.
     *
     * @param array<string, mixed> $options Image display options
     */
    public function addImage(int $windowNo, string $imagePath, array $options = []): self
    {
        $this->validateWindow($windowNo);

        if (!file_exists($imagePath)) {
            throw new FileNotFoundException("Image not found: {$imagePath}");
        }

        $defaultOptions = [
            'x' => 0,
            'y' => 0,
            'mode' => ImageMode::CENTER,
            'effect' => Effect::DRAW,
            'speed' => 5,
            'stay' => 10,
        ];

        $options = array_merge($defaultOptions, $options);

        $this->windowContent[$windowNo][] = [
            'type' => 'image',
            'content' => $imagePath,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Add clock to a window.
     *
     * @param array<string, mixed> $options Clock display options
     */
    public function addClock(int $windowNo, array $options = []): self
    {
        $this->validateWindow($windowNo);

        $defaultOptions = [
            'font' => FontSize::FONT_16,
            'color' => Color::RED,
            'format' => Protocol::CLOCK_24_HOUR,
            'effect' => Effect::DRAW,
            'speed' => 5,
            'stay' => 10,
        ];

        $options = array_merge($defaultOptions, $options);

        $this->windowContent[$windowNo][] = [
            'type' => 'clock',
            'content' => '',
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Display the program.
     */
    public function display(): self
    {
        foreach ($this->windows as $windowNo => $window) {
            if (isset($this->windowContent[$windowNo])) {
                foreach ($this->windowContent[$windowNo] as $content) {
                    $this->sendWindowContent($windowNo, $content);
                }
            }
        }

        return $this;
    }

    /**
     * Send content to a window.
     *
     * @param array<string, mixed> $content Window content data
     */
    private function sendWindowContent(int $windowNo, array $content): void
    {
        switch ($content['type']) {
            case 'text':
                $packet = PacketBuilder::createTextPacket(
                    $this->controller->getConfig()['cardId'],
                    $windowNo,
                    $content['content'],
                    $content['options'],
                );

                break;

            case 'image':
                $imageData = file_get_contents($content['content']);
                $packet = PacketBuilder::createImagePacket(
                    $this->controller->getConfig()['cardId'],
                    $windowNo,
                    $imageData,
                    $content['options'],
                );

                break;

            case 'clock':
                $packet = PacketBuilder::createClockPacket(
                    $this->controller->getConfig()['cardId'],
                    $windowNo,
                    $content['options'],
                );

                break;

            default:
                throw new ValidationException("Unknown content type: {$content['type']}");
        }

        $this->controller->sendPacket($packet);
    }

    /**
     * Validate window exists.
     */
    private function validateWindow(int $windowNo): void
    {
        if (!isset($this->windows[$windowNo])) {
            throw new ValidationException("Window {$windowNo} not defined");
        }
    }

    /**
     * Get program data.
     *
     * @return array<int, mixed> Program data
     */
    public function getProgram(): array
    {
        return $this->program;
    }
}
