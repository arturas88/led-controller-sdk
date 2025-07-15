<?php

namespace LEDController;

use LEDController\Interface\CommunicationInterface;
use LEDController\Communication\NetworkCommunication;
use LEDController\Communication\SerialCommunication;
use LEDController\Manager\TemplateManager;
use LEDController\Manager\FileManager;
use LEDController\Manager\ScheduleManager;
use LEDController\Manager\ConfigManager;
use LEDController\Manager\SetupManager;
use LEDController\Manager\ExternalCallsManager;
use LEDController\Manager\ClockManager;
use LEDController\Manager\TemperatureManager;
use LEDController\Exception\ConnectionException;
use LEDController\Exception\FileNotFoundException;
use LEDController\Exception\CommunicationException;
use LEDController\Exception\ValidationException;
use LEDController\TextProcessor;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\VerticalAlignment;
use LEDController\Enum\ImageMode;
use LEDController\ProgramBuilder;
use LEDController\ControllerStatus;
use LEDController\PacketBuilder;
use LEDController\Logger;
use LEDController\Packet;
use LEDController\Response;

/**
 * Main LED Controller class with fluent interface
 */
class LEDController
{
    private CommunicationInterface $communication;
    private array $config;
    private ?ProgramBuilder $programBuilder = null;
    private ?TemplateManager $templateManager = null;
    private ?FileManager $fileManager = null;
    private ?ScheduleManager $scheduleManager = null;
    private ?ConfigManager $configManager = null;
    private ?SetupManager $setupManager = null;
    private ?ExternalCallsManager $externalCallsManager = null;
    private ?ClockManager $clockManager = null;
    private ?TemperatureManager $temperatureManager = null;
    private bool $connected = false;

    /**
     * Default configuration
     */
    private const DEFAULT_CONFIG = [
        'ip' => '192.168.10.61',
        'port' => 5200,
        'cardId' => 1,
        'networkIdCode' => 0xFFFFFFFF, // Updated to correct network ID as specified by user
        'timeout' => 5000,
        'retries' => 3,
        'communicationType' => 'network',
        'serialPort' => 'COM1',
        'baudRate' => 115200,
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->initializeCommunication();
    }

    private function initializeCommunication(): void
    {
        if ($this->config['communicationType'] === 'network') {
            $this->communication = new NetworkCommunication($this->config);
        } else {
            $this->communication = new SerialCommunication($this->config);
        }
    }

    /**
     * Connect to the LED controller
     */
    public function connect(): self
    {
        if (!$this->connected) {
            $this->connected = $this->communication->connect();
            if (!$this->connected) {
                throw new ConnectionException("Failed to connect to controller");
            }
        }
        return $this;
    }

    /**
     * Disconnect from the LED controller
     */
    public function disconnect(): void
    {
        if ($this->connected) {
            $this->communication->disconnect();
            $this->connected = false;
        }
    }

    /**
     * Create a new program builder
     */
    public function program(): ProgramBuilder
    {
        $this->ensureConnected();
        if (!$this->programBuilder) {
            $this->programBuilder = new ProgramBuilder($this);
        }
        return $this->programBuilder;
    }

    /**
     * Access template manager
     */
    public function template(): TemplateManager
    {
        $this->ensureConnected();
        if (!$this->templateManager) {
            $this->templateManager = new TemplateManager($this);
        }
        return $this->templateManager;
    }

    /**
     * Access file manager
     */
    public function files(): FileManager
    {
        $this->ensureConnected();
        if (!$this->fileManager) {
            $this->fileManager = new FileManager($this);
        }
        return $this->fileManager;
    }

    /**
     * Access schedule manager
     */
    public function schedule(): ScheduleManager
    {
        $this->ensureConnected();
        if (!$this->scheduleManager) {
            $this->scheduleManager = new ScheduleManager($this);
        }
        return $this->scheduleManager;
    }

    /**
     * Access configuration manager
     */
    public function config(): ConfigManager
    {
        if (!$this->configManager) {
            $this->configManager = new ConfigManager();
        }
        return $this->configManager;
    }

    /**
     * Access setup manager
     */
    public function setup(): SetupManager
    {
        $this->ensureConnected();
        if (!$this->setupManager) {
            $this->setupManager = new SetupManager($this);
        }
        return $this->setupManager;
    }

    /**
     * Access external calls manager
     */
    public function external(): ExternalCallsManager
    {
        $this->ensureConnected();
        if (!$this->externalCallsManager) {
            $this->externalCallsManager = new ExternalCallsManager($this);
        }
        return $this->externalCallsManager;
    }

    /**
     * Get clock manager
     */
    public function clock(): ClockManager
    {
        $this->ensureConnected();
        if (!$this->clockManager) {
            $this->clockManager = new ClockManager($this);
        }
        return $this->clockManager;
    }

    /**
     * Get temperature manager
     */
    public function temperature(): TemperatureManager
    {
        $this->ensureConnected();
        if (!$this->temperatureManager) {
            $this->temperatureManager = new TemperatureManager($this);
        }
        return $this->temperatureManager;
    }

    /**
     * Display text with modern features - single source of truth
     *
     * @param string $text The text to display
     * @param array $options Display options (font, color, effect, window, etc.)
     */
    public function displayText(string $text, array $options = []): self
    {
        $this->ensureConnected();

        $defaultOptions = [
            'window' => 0, // Window ID (0-15), defaults to 0 for single window usage
            'font' => FontSize::FONT_16,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'speed' => 15,
            'stay' => 5,
            'align' => Alignment::LEFT,
            'valign' => VerticalAlignment::TOP,
            'fontStyle' => 0,
            'mode' => TextProcessor::MODE_TEXT // Default: send text as-is
        ];

        $options = array_merge($defaultOptions, $options);

        // Validate window ID
        if ($options['window'] < 0 || $options['window'] > 15) {
            throw new ValidationException("Window ID must be between 0 and 15");
        }

        // Process text based on explicit mode
        $processor = new TextProcessor();
        $result = $processor->processText($text, $options);

        // Display based on result type
        if ($result['type'] === 'image') {
            return $this->displayImageData($result['content'], $options);
        } else {
            // Universal color support - always use Pure Text packet for full compatibility
            $packet = PacketBuilder::createPureTextPacket(
                $this->config['cardId'],
                $options['window'],
                $result['content'],
                $options
            );

            $this->sendPacket($packet);
        }

        return $this;
    }

    /**
     * Display text with explicit transliteration
     */
    public function displayTextTransliterated(string $text, array $options = []): self
    {
        $options['mode'] = TextProcessor::MODE_TRANSLITERATE;
        return $this->displayText($text, $options);
    }

    /**
     * Display text as image
     */
    public function displayTextAsImage(string $text, array $options = []): self
    {
        $options['mode'] = TextProcessor::MODE_TO_IMAGE;
        return $this->displayText($text, $options);
    }

    /**
     * Quick image display
     */
    public function displayImage(string $imagePath, array $options = []): self
    {
        $this->ensureConnected();

        $defaultOptions = [
            'window' => 0,
            'x' => 0,
            'y' => 0,
            'mode' => ImageMode::CENTER,
            'speed' => 5,
            'stay' => 10
        ];

        $options = array_merge($defaultOptions, $options);

        if (!file_exists($imagePath)) {
            throw new FileNotFoundException("Image file not found: $imagePath");
        }

        $imageData = file_get_contents($imagePath);

        $packet = PacketBuilder::createImagePacket(
            $this->config['cardId'],
            $options['window'],
            $imageData,
            $options
        );

        $this->sendPacket($packet);

        return $this;
    }



    /**
     * Set controller time
     */
    public function setTime(?\DateTime $dateTime = null): self
    {
        $this->ensureConnected();

        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        $packet = PacketBuilder::createTimeSetPacket($this->config['cardId'], $dateTime);
        $this->sendPacket($packet);

        return $this;
    }

    /**
     * Get controller time
     */
    public function getTime(): \DateTime
    {
        $this->ensureConnected();

        try {
            $packet = PacketBuilder::createTimeQueryPacket($this->config['cardId']);
            $response = $this->sendPacket($packet);

            if (!$response->isSuccess()) {
                throw new CommunicationException("Failed to get time: " . $response->getReturnCodeMessage());
            }

            return $response->getDateTime();
        } catch (\Exception $e) {
            // Log the error for debugging
            Logger::getInstance()->error("Time query failed: " . $e->getMessage());

            // Return current system time as fallback
            return new \DateTime();
        }
    }

    /**
     * Set brightness
     */
    public function setBrightness(int $brightness, int $hour = -1): self
    {
        $this->ensureConnected();

        if ($brightness < 0 || $brightness > 31) {
            throw new ValidationException("Brightness must be between 0 and 31");
        }

        $packet = PacketBuilder::createBrightnessSetPacket($this->config['cardId'], $brightness, $hour);
        $this->sendPacket($packet);

        return $this;
    }

    /**
     * Clear the display
     */
    public function clearDisplay(): self
    {
        $this->ensureConnected();

        // Simply exit split screen mode
        $packet = PacketBuilder::createExitSplitScreenPacket($this->config['cardId']);
        $this->sendPacket($packet);

        return $this;
    }

    /**
     * Reset display to clean state
     * This method should be called when starting a new example/session
     */
    public function resetDisplay(): self
    {
        $this->ensureConnected();

        // Clear display first
        $this->clearDisplay();

        // Reset brightness to a good default level
        $this->setBrightness(25);

        // Send a reset packet with known good defaults
        $resetPacket = PacketBuilder::createPureTextPacket(
            $this->config['cardId'],
            0, // window 0
            ' ', // single space to ensure state reset
            [
                'color' => Color::WHITE,
                'font' => FontSize::FONT_16,
                'effect' => Effect::DRAW,
                'speed' => 5,
                'stay' => 100, // Very short display time
                'align' => Alignment::LEFT
            ]
        );
        $this->sendPacket($resetPacket);

        // Wait a moment then clear again
        usleep(200000); // 200ms
        $this->clearDisplay();

        return $this;
    }

    /**
     * Get controller status
     */
    public function getStatus(): ControllerStatus
    {
        $this->ensureConnected();

        $status = new ControllerStatus();

        // Get version info
        $versionPacket = PacketBuilder::createVersionQueryPacket($this->config['cardId']);
        $versionResponse = $this->sendPacket($versionPacket);
        if ($versionResponse->isSuccess()) {
            $status->setVersionInfo($versionResponse->getVersionInfo());
        }

        // Get temperature
        $tempPacket = PacketBuilder::createTemperatureQueryPacket($this->config['cardId']);
        $tempResponse = $this->sendPacket($tempPacket);
        if ($tempResponse->isSuccess()) {
            $status->setTemperature($tempResponse->getTemperature());
        }

        // Get free space
        $spacePacket = PacketBuilder::createDiskSpaceQueryPacket($this->config['cardId']);
        $spaceResponse = $this->sendPacket($spacePacket);
        if ($spaceResponse->isSuccess()) {
            $status->setFreeSpace($spaceResponse->getFreeSpace());
        }

        return $status;
    }

    /**
     * Restart controller
     */
    public function restart(bool $hardware = false): self
    {
        $this->ensureConnected();

        if ($hardware) {
            $packet = PacketBuilder::createHardwareRestartPacket($this->config['cardId']);
        } else {
            $packet = PacketBuilder::createAppRestartPacket($this->config['cardId']);
        }

        $this->sendPacket($packet);

        return $this;
    }

    /**
     * Send packet to controller
     */
    public function sendPacket(Packet $packet): Response
    {
        $this->ensureConnected();

        $maxRetries = $this->config['retries'];
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $response = $this->communication->send($packet);

                // Log successful communication
                Logger::getInstance()->protocol('sent', $packet->build());
                Logger::getInstance()->protocol('received', $response->getData());

                return $response;
            } catch (CommunicationException $e) {
                $retryCount++;

                // Log failed communication
                Logger::getInstance()->error("Communication failed (retry $retryCount/$maxRetries): " . $e->getMessage());

                if ($retryCount >= $maxRetries) {
                    throw $e;
                }

                // Wait before retry
                usleep(100000); // 100ms
            }
        }

        throw new CommunicationException("Max retries reached");
    }

    /**
     * Get communication interface
     */
    public function getCommunication(): CommunicationInterface
    {
        return $this->communication;
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if controller is connected
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Ensure connected
     */
    private function ensureConnected(): void
    {
        if (!$this->connected) {
            $this->connect();
        }
    }

    /**
     * Display image data directly (binary data, not file path)
     */
    public function displayImageData(string $imageData, array $options = []): self
    {
        $this->ensureConnected();

        $defaultOptions = [
            'window' => 0,
            'x' => 0,
            'y' => 0,
            'effect' => Effect::DRAW,
            'speed' => 5,
            'stay' => 10,
            'mode' => ImageMode::CENTER
        ];

        $options = array_merge($defaultOptions, $options);

        // Force image mode to be an integer (override TextProcessor mode)
        $options['mode'] = ImageMode::CENTER;

        // Filter options to only include image-specific ones
        // This prevents TextProcessor options from interfering with image packet creation
        $imageOptions = [
            'window' => $options['window'],
            'x' => $options['x'],
            'y' => $options['y'],
            'effect' => $options['effect'],
            'speed' => $options['speed'],
            'stay' => $options['stay'],
            'mode' => $options['mode']
        ];

        // Create image packet with binary data
        $packet = PacketBuilder::createImagePacket(
            $this->config['cardId'],
            $imageOptions['window'],
            $imageData,
            $imageOptions
        );

        $response = $this->sendPacket($packet);

        if (!$response->isSuccess()) {
            throw new CommunicationException("Failed to display image: " . $response->getReturnCodeMessage());
        }

        return $this;
    }
}
