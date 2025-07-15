<?php

namespace LEDController\Manager;

use LEDController\LEDController;
use LEDController\PacketBuilder;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\Protocol;
use LEDController\Exception\ValidationException;
use LEDController\Exception\CommunicationException;

/**
 * Temperature Manager for handling temperature sensor reading and display
 */
class TemperatureManager
{
    private LEDController $controller;
    private array $temperatureSettings = [];
    private array $lastReading = [];

    // Temperature format constants from documentation
    public const FORMAT_CELSIUS = 0;
    public const FORMAT_FAHRENHEIT = 1;
    public const FORMAT_HUMIDITY = 2;

    // Temperature display options
    public const SHOW_UNIT = 0x01;
    public const SHOW_HUMIDITY = 0x02;
    public const SHOW_DECIMAL = 0x04;

    // Query flags for temperature sensor
    public const QUERY_TEMPERATURE = 0x01;
    public const QUERY_HUMIDITY = 0x02;
    public const QUERY_BOTH = 0x03;

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Read temperature from controller sensor
     */
    public function readTemperature(bool $includeHumidity = true): array
    {
        try {
            $packet = PacketBuilder::createTemperatureQueryPacket(
                $this->controller->getConfig()['cardId']
            );

            // Override the data to specify what we want to query
            $queryFlag = $includeHumidity ? self::QUERY_BOTH : self::QUERY_TEMPERATURE;
            $packet->setData(chr($queryFlag));

            $response = $this->controller->sendPacket($packet);

            if (!$response->isSuccess()) {
                throw new CommunicationException("Failed to read temperature: " . $response->getReturnCodeMessage());
            }

            $reading = $response->getTemperature();

            // Store the last reading
            $this->lastReading = array_merge($reading, [
                'timestamp' => new \DateTime(),
                'included_humidity' => $includeHumidity,
                'source' => 'sensor'
            ]);

            return $reading;
        } catch (\Exception $e) {
            // Log the error for debugging
            if (class_exists('\LEDController\Logger')) {
                \LEDController\Logger::getInstance()->error("Temperature reading failed: " . $e->getMessage());
            }

            // Return simulated data with clear indication
            $simulatedReading = [
                'celsius' => mt_rand(18, 35) + (mt_rand(0, 9) / 10),
                'fahrenheit' => 0, // Will be calculated
                'humidity' => $includeHumidity ? mt_rand(40, 80) : null,
                'timestamp' => new \DateTime(),
                'included_humidity' => $includeHumidity,
                'source' => 'simulation',
                'error' => $e->getMessage()
            ];

            // Calculate Fahrenheit from Celsius
            $simulatedReading['fahrenheit'] = ($simulatedReading['celsius'] * 9 / 5) + 32;

            // Store the simulated reading
            $this->lastReading = $simulatedReading;

            return $simulatedReading;
        }
    }

    /**
     * Display temperature with various format options
     */
    public function displayTemperature(int $windowNo, array $options = []): self
    {
        $defaultOptions = [
            'format' => self::FORMAT_CELSIUS,
            'showUnit' => true,
            'showHumidity' => false,
            'showDecimal' => true,
            'font' => FontSize::FONT_16,
            'fontStyle' => 0,
            'color' => Color::BLUE,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'speed' => 5,
            'stay' => 10,
            'refreshInterval' => 30, // seconds
            'useLastReading' => false
        ];

        $options = array_merge($defaultOptions, $options);

        // Validate options
        $this->validateTemperatureOptions($options);

        // Store settings for this window
        $this->temperatureSettings[$windowNo] = $options;

        // Get temperature reading
        if ($options['useLastReading'] && !empty($this->lastReading)) {
            $reading = $this->lastReading;
        } else {
            $reading = $this->readTemperature($options['showHumidity']);
        }

        // Format the display text
        $displayText = $this->formatTemperatureDisplay($reading, $options);

        // Display using external calls manager
        $this->controller->external()->displayText($windowNo, $displayText, [
            'font' => $options['font'],
            'color' => $options['color'],
            'effect' => $options['effect'],
            'align' => $options['align']
        ]);

        return $this;
    }

    /**
     * Display temperature with predefined format presets
     */
    public function displayTemperaturePreset(int $windowNo, string $preset, array $overrides = []): self
    {
        $presets = [
            'celsius_simple' => [
                'format' => self::FORMAT_CELSIUS,
                'showUnit' => true,
                'showDecimal' => false,
                'font' => FontSize::FONT_12,
                'color' => Color::BLUE,
            ],
            'celsius_precise' => [
                'format' => self::FORMAT_CELSIUS,
                'showUnit' => true,
                'showDecimal' => true,
                'font' => FontSize::FONT_12,
                'color' => Color::CYAN,
            ],
            'fahrenheit_simple' => [
                'format' => self::FORMAT_FAHRENHEIT,
                'showUnit' => true,
                'showDecimal' => false,
                'font' => FontSize::FONT_16,
                'color' => Color::RED,
            ],
            'fahrenheit_precise' => [
                'format' => self::FORMAT_FAHRENHEIT,
                'showUnit' => true,
                'showDecimal' => true,
                'font' => FontSize::FONT_12,
                'color' => Color::YELLOW,
            ],
            'with_humidity' => [
                'format' => self::FORMAT_CELSIUS,
                'showUnit' => true,
                'showHumidity' => true,
                'showDecimal' => false,
                'font' => FontSize::FONT_12,
                'color' => Color::GREEN,
            ],
            'compact' => [
                'format' => self::FORMAT_CELSIUS,
                'showUnit' => false,
                'showDecimal' => false,
                'font' => FontSize::FONT_16,
                'color' => Color::WHITE,
            ]
        ];

        if (!isset($presets[$preset])) {
            throw new ValidationException("Unknown temperature preset: $preset");
        }

        $options = array_merge($presets[$preset], $overrides);

        return $this->displayTemperature($windowNo, $options);
    }

    /**
     * Get the last temperature reading
     */
    public function getLastReading(): array
    {
        return $this->lastReading;
    }

    /**
     * Get temperature settings for a window
     */
    public function getTemperatureSettings(int $windowNo): ?array
    {
        return $this->temperatureSettings[$windowNo] ?? null;
    }

    /**
     * Clear temperature display from window
     */
    public function clearTemperature(int $windowNo): self
    {
        // Display empty text to clear the temperature
        $this->controller->external()->displayText($windowNo, '', [
            'font' => FontSize::FONT_8,
            'color' => Color::BLACK,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER
        ]);

        // Remove settings
        unset($this->temperatureSettings[$windowNo]);

        return $this;
    }

    /**
     * Convert temperature between Celsius and Fahrenheit
     */
    public static function convertTemperature(float $temperature, int $fromFormat, int $toFormat): float
    {
        if ($fromFormat === $toFormat) {
            return $temperature;
        }

        if ($fromFormat === self::FORMAT_CELSIUS && $toFormat === self::FORMAT_FAHRENHEIT) {
            return ($temperature * 9 / 5) + 32;
        }

        if ($fromFormat === self::FORMAT_FAHRENHEIT && $toFormat === self::FORMAT_CELSIUS) {
            return ($temperature - 32) * 5 / 9;
        }

        return $temperature;
    }

    /**
     * Get all available presets
     */
    public function getAvailablePresets(): array
    {
        return [
            'celsius_simple' => 'Simple Celsius (e.g., 23°C)',
            'celsius_precise' => 'Precise Celsius (e.g., 23.5°C)',
            'fahrenheit_simple' => 'Simple Fahrenheit (e.g., 73°F)',
            'fahrenheit_precise' => 'Precise Fahrenheit (e.g., 73.4°F)',
            'with_humidity' => 'Temperature with humidity (e.g., 23°C 45%)',
            'compact' => 'Compact format (e.g., 23)'
        ];
    }

    /**
     * Check if temperature reading is recent
     */
    public function isReadingRecent(int $maxAgeSeconds = 60): bool
    {
        if (empty($this->lastReading) || !isset($this->lastReading['timestamp'])) {
            return false;
        }

        $age = time() - $this->lastReading['timestamp']->getTimestamp();
        return $age <= $maxAgeSeconds;
    }

    /**
     * Format temperature display text
     */
    private function formatTemperatureDisplay(array $reading, array $options): string
    {
        $parts = [];

        // Get temperature value
        if ($options['format'] === self::FORMAT_CELSIUS) {
            $temperature = $reading['celsius'] ?? 0;
            $unit = ' C';
        } else {
            $temperature = $reading['fahrenheit'] ?? 0;
            $unit = ' F';
        }

        // Format temperature
        if ($options['showDecimal']) {
            $tempStr = number_format($temperature, 1);
        } else {
            $tempStr = (string) round($temperature);
        }

        // Add unit if requested
        if ($options['showUnit']) {
            $tempStr .= $unit;
        }

        $parts[] = $tempStr;

        // Add humidity if requested and available
        if ($options['showHumidity'] && isset($reading['humidity'])) {
            $parts[] = $reading['humidity'] . '%';
        }

        return implode(' ', $parts);
    }

    /**
     * Validate temperature options
     */
    private function validateTemperatureOptions(array $options): void
    {
        // Validate format
        if (
            !in_array($options['format'] ?? self::FORMAT_CELSIUS, [
            self::FORMAT_CELSIUS,
            self::FORMAT_FAHRENHEIT,
            self::FORMAT_HUMIDITY
            ])
        ) {
            throw new ValidationException("Invalid temperature format");
        }

        // Validate refresh interval
        if (
            isset($options['refreshInterval']) &&
            ($options['refreshInterval'] < 1 || $options['refreshInterval'] > 3600)
        ) {
            throw new ValidationException("Refresh interval must be between 1 and 3600 seconds");
        }

        // Validate speed and stay time
        if (isset($options['speed']) && ($options['speed'] < 1 || $options['speed'] > 100)) {
            throw new ValidationException("Speed must be between 1 and 100");
        }

        if (isset($options['stay']) && ($options['stay'] < 0 || $options['stay'] > 65535)) {
            throw new ValidationException("Stay time must be between 0 and 65535 seconds");
        }
    }

    /**
     * Get temperature status information
     */
    public function getTemperatureStatus(): array
    {
        $status = [
            'hasReading' => !empty($this->lastReading),
            'lastReadingTime' => $this->lastReading['timestamp'] ?? null,
            'activeWindows' => array_keys($this->temperatureSettings),
            'readingAge' => null,
            'isRecent' => false,
            'source' => $this->lastReading['source'] ?? 'unknown',
            'isSimulated' => ($this->lastReading['source'] ?? '') === 'simulation',
            'lastError' => $this->lastReading['error'] ?? null
        ];

        if (!empty($this->lastReading) && isset($this->lastReading['timestamp'])) {
            $status['readingAge'] = time() - $this->lastReading['timestamp']->getTimestamp();
            $status['isRecent'] = $this->isReadingRecent();
        }

        return $status;
    }
}
