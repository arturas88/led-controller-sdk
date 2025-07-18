<?php

declare(strict_types=1);

namespace LEDController\Builder;

use LEDController\Enum\Protocol;
use LEDController\Manager\TemplateManager;

/**
 * Template builder for fluent interface.
 */
class TemplateBuilder
{
    private TemplateManager $manager;

    private int $templateId;

    public function __construct(TemplateManager $manager, int $templateId)
    {
        $this->manager = $manager;
        $this->templateId = $templateId;
    }

    /**
     * Create template with dimensions.
     */
    public function create(int $width, int $height, int $colorMode = Protocol::COLOR_FULL): self
    {
        $this->manager->create($this->templateId, $width, $height, $colorMode);

        return $this;
    }

    /**
     * Add window to template.
     */
    public function addWindow(int $windowId, int $x, int $y, int $width, int $height, int $windowType): self
    {
        $this->manager->addWindow($this->templateId, $windowId, $x, $y, $width, $height, $windowType);

        return $this;
    }

    /**
     * Send text to window.
     *
     * @param array<string, mixed> $options Text display options
     *
     * @return $this
     */
    public function sendText(int $windowId, string $text, array $options = []): self
    {
        $this->manager->sendText($this->templateId, $windowId, $text, $options);

        return $this;
    }

    /**
     * Send image to window.
     *
     * @param array<string, mixed> $options Image display options
     *
     * @return $this
     */
    public function sendImage(int $windowId, string $imageData, array $options = []): self
    {
        $this->manager->sendImage($this->templateId, $windowId, $imageData, $options);

        return $this;
    }

    /**
     * Send image file to window.
     *
     * @param array<string, mixed> $options Image display options
     *
     * @return $this
     */
    public function sendImageFile(int $windowId, string $imagePath, array $options = []): self
    {
        $this->manager->sendImageFile($this->templateId, $windowId, $imagePath, $options);

        return $this;
    }

    /**
     * Set template properties.
     *
     * @param array<string, mixed> $properties Template properties
     *
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->manager->setProperties($this->templateId, $properties);

        return $this;
    }

    /**
     * Activate template.
     */
    public function activate(): self
    {
        $this->manager->activate($this->templateId);

        return $this;
    }

    /**
     * Delete template.
     */
    public function delete(): self
    {
        $this->manager->delete($this->templateId);

        return $this;
    }

    /**
     * Get template manager.
     */
    public function getManager(): TemplateManager
    {
        return $this->manager;
    }
}
