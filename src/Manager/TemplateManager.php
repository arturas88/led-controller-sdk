<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\Enum\Protocol;
use LEDController\Enum\Color;
use LEDController\Enum\FontSize;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Command;
use LEDController\Builder\TemplateBuilder;
use LEDController\Exception\TemplateException;
use LEDController\Packet;

/**
 * Template manager for creating and managing display templates
 */
class TemplateManager
{
    private LEDController $controller;
    private array $templates = [];
    private ?int $activeTemplate = null;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Create a new template
     */
    public function create(int $templateId, int $width, int $height, int $colorMode = Protocol::COLOR_FULL): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_CREATE);

        $data = chr($templateId);
        $data .= pack('n', $width);  // Big-endian
        $data .= pack('n', $height); // Big-endian
        $data .= chr($colorMode);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to create template: " . $response->getReturnCodeMessage());
        }

        $this->templates[$templateId] = [
            'width' => $width,
            'height' => $height,
            'colorMode' => $colorMode,
            'windows' => []
        ];

        return $this;
    }

    /**
     * Delete a template
     */
    public function delete(int $templateId): self
    {
        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_DELETE);

        $data = chr($templateId);
        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to delete template: " . $response->getReturnCodeMessage());
        }

        if ($this->activeTemplate === $templateId) {
            $this->activeTemplate = null;
        }

        unset($this->templates[$templateId]);

        return $this;
    }

    /**
     * Add a window to a template
     */
    public function addWindow(int $templateId, int $windowId, int $x, int $y, int $width, int $height, int $windowType): self
    {
        $this->validateTemplate($templateId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_CREATE);

        $data = chr($templateId);
        $data .= pack('n', $this->templates[$templateId]['width']);  // Template width
        $data .= pack('n', $this->templates[$templateId]['height']); // Template height
        $data .= chr($this->templates[$templateId]['colorMode']);

        // Add window definition
        $data .= chr($windowId);
        $data .= pack('n', $x);      // Big-endian
        $data .= pack('n', $y);      // Big-endian
        $data .= pack('n', $width);  // Big-endian
        $data .= pack('n', $height); // Big-endian
        $data .= chr($windowType);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to add window: " . $response->getReturnCodeMessage());
        }

        $this->templates[$templateId]['windows'][$windowId] = [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'type' => $windowType
        ];

        return $this;
    }

    /**
     * Send text to a template window
     */
    public function sendText(int $templateId, int $windowId, string $text, array $options = []): self
    {
        $this->validateTemplate($templateId);
        $this->validateWindow($templateId, $windowId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_SEND_TEXT);

        $data = chr($templateId);
        $data .= chr($windowId);

        // Effect
        $effect = $options['effect'] ?? Effect::DRAW;
        $data .= chr($effect);

        // Font
        $font = $options['font'] ?? FontSize::FONT_16;
        $data .= chr($font);

        // Color - Universal color support: hex strings, RGB arrays, or color constants
        $color = $options['color'] ?? Color::RED;
        $rgbColor = Color::convert($color);

        $data .= chr(0x77); // RGB mode
        $data .= chr($rgbColor['r']);
        $data .= chr($rgbColor['g']);
        $data .= chr($rgbColor['b']);

        // Alignment
        $align = $options['align'] ?? Alignment::LEFT;
        $data .= chr($align);

        // Speed and stay time
        $speed = $options['speed'] ?? 5;
        $stay = $options['stay'] ?? 10;
        $data .= chr($speed);
        $data .= pack('n', $stay); // Big-endian

        // Text content - send as-is by default
        $data .= $text;
        $data .= chr(0x00); // Null terminator

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to send text: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Send image to a template window
     */
    public function sendImage(int $templateId, int $windowId, string $imageData, array $options = []): self
    {
        $this->validateTemplate($templateId);
        $this->validateWindow($templateId, $windowId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_SEND_IMAGE);

        $data = chr($templateId);
        $data .= chr($windowId);

        // Effect
        $effect = $options['effect'] ?? Effect::DRAW;
        $data .= chr($effect);

        // Image mode
        $mode = $options['mode'] ?? ImageMode::CENTER;
        $data .= chr($mode);

        // Position
        $x = $options['x'] ?? 0;
        $y = $options['y'] ?? 0;
        $data .= pack('n', $x); // Big-endian
        $data .= pack('n', $y); // Big-endian

        // Speed and stay time
        $speed = $options['speed'] ?? 5;
        $stay = $options['stay'] ?? 10;
        $data .= chr($speed);
        $data .= pack('n', $stay); // Big-endian

        // Image data
        $data .= $imageData;

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to send image: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Send image file to a template window
     */
    public function sendImageFile(int $templateId, int $windowId, string $imagePath, array $options = []): self
    {
        if (!file_exists($imagePath)) {
            throw new TemplateException("Image file not found: $imagePath");
        }

        $imageData = file_get_contents($imagePath);

        return $this->sendImage($templateId, $windowId, $imageData, $options);
    }

    /**
     * Set template properties
     */
    public function setProperties(int $templateId, array $properties): self
    {
        $this->validateTemplate($templateId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_PROPERTY);

        $data = chr($templateId);

        // Play time
        if (isset($properties['playTime'])) {
            $data .= pack('n', $properties['playTime']); // Big-endian
        } else {
            $data .= pack('n', 10); // Default 10 seconds
        }

        // Play count
        if (isset($properties['playCount'])) {
            $data .= pack('n', $properties['playCount']); // Big-endian
        } else {
            $data .= pack('n', 1); // Default 1 time
        }

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to set properties: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Template play control
     */
    public function playControl(int $templateId, int $action): self
    {
        $this->validateTemplate($templateId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_PLAY_CONTROL);

        $data = chr($templateId);
        $data .= chr($action);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to control template: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Play a program in template
     */
    public function playProgram(int $templateId, int $programId): self
    {
        $this->validateTemplate($templateId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_PLAY_PROGRAM);

        $data = chr($templateId);
        $data .= chr($programId);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to play program: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Play a playbill in template
     */
    public function playPlaybill(int $templateId, int $playbillId): self
    {
        $this->validateTemplate($templateId);

        $packet = new Packet($this->controller->getConfig()['cardId'], Command::TEMPLATE_PLAY_PLAYBILL);

        $data = chr($templateId);
        $data .= chr($playbillId);

        $packet->setData($data);

        $response = $this->controller->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new TemplateException("Failed to play playbill: " . $response->getReturnCodeMessage());
        }

        return $this;
    }

    /**
     * Activate template
     */
    public function activate(int $templateId): self
    {
        $this->validateTemplate($templateId);

        $this->playControl($templateId, 0x01); // Play
        $this->activeTemplate = $templateId;

        return $this;
    }

    /**
     * Stop active template
     */
    public function stop(): self
    {
        if ($this->activeTemplate !== null) {
            $this->playControl($this->activeTemplate, 0x02); // Stop
            $this->activeTemplate = null;
        }

        return $this;
    }

    /**
     * Pause active template
     */
    public function pause(): self
    {
        if ($this->activeTemplate !== null) {
            $this->playControl($this->activeTemplate, 0x03); // Pause
        }

        return $this;
    }

    /**
     * Get template information
     */
    public function getTemplate(int $templateId): ?array
    {
        return $this->templates[$templateId] ?? null;
    }

    /**
     * Get all templates
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Get active template ID
     */
    public function getActiveTemplate(): ?int
    {
        return $this->activeTemplate;
    }

    /**
     * Clear all templates
     */
    public function clearAll(): self
    {
        foreach (array_keys($this->templates) as $templateId) {
            $this->delete($templateId);
        }

        return $this;
    }

    /**
     * Fluent interface for template creation
     */
    public function template(int $templateId): TemplateBuilder
    {
        return new TemplateBuilder($this, $templateId);
    }

    /**
     * Validate template exists
     */
    private function validateTemplate(int $templateId): void
    {
        if (!isset($this->templates[$templateId])) {
            throw new TemplateException("Template $templateId does not exist");
        }
    }

    /**
     * Validate window exists in template
     */
    private function validateWindow(int $templateId, int $windowId): void
    {
        if (!isset($this->templates[$templateId]['windows'][$windowId])) {
            throw new TemplateException("Window $windowId does not exist in template $templateId");
        }
    }
}
