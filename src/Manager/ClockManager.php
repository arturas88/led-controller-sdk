<?php

declare(strict_types=1);

namespace LEDController\Manager;

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Exception\ValidationException;
use LEDController\LEDController;
use LEDController\PacketBuilder;

/**
 * Clock Manager for handling various clock display formats and options.
 */
class ClockManager
{
    // Clock format constants from documentation
    public const FORMAT_12_HOUR = 0;
    public const FORMAT_24_HOUR = 1;
    public const YEAR_4_DIGIT = 0;
    public const YEAR_2_DIGIT = 1;
    public const SINGLE_ROW = 0;
    public const MULTI_ROW = 1;
    public const SHOW_TIME_SCALE = 0x40;

    // Clock content flags from documentation
    public const SHOW_YEAR = 0x01;
    public const SHOW_MONTH = 0x02;
    public const SHOW_DAY = 0x04;
    public const SHOW_HOUR = 0x08;
    public const SHOW_MINUTE = 0x10;
    public const SHOW_SECOND = 0x20;
    public const SHOW_WEEKDAY = 0x40;
    public const SHOW_POINTER = 0x80;

    // Calendar types from documentation
    public const CALENDAR_GREGORIAN = 0;
    public const CALENDAR_LUNAR = 1;
    public const CALENDAR_LUNAR_SOLAR = 2;
    public const CALENDAR_LUNAR_SOLAR_TERMS = 3;

    private LEDController $controller;

    /**
     * @var array<int, array<string, mixed>> Clock display settings
     */
    private array $clockSettings = [];

    // GB2312 font compatibility settings
    private bool $gb2312Validation = true;

    /**
     * @var array<string, string> GB2312 safe format patterns
     */
    private array $gb2312SafeFormats = [];

    public function __construct(LEDController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Enable or disable GB2312 character validation.
     */
    public function setGB2312Validation(bool $enabled): self
    {
        $this->gb2312Validation = $enabled;

        return $this;
    }

    /**
     * Display clock with various format options.
     *
     * @param array<string, mixed> $options Clock display options
     */
    public function displayClock(int $windowNo, array $options = []): self
    {
        $defaultOptions = [
            'format' => self::FORMAT_24_HOUR,
            'yearFormat' => self::YEAR_4_DIGIT,
            'rowFormat' => self::SINGLE_ROW,
            'showTimeScale' => false,
            'content' => self::SHOW_HOUR | self::SHOW_MINUTE,
            'calendar' => self::CALENDAR_GREGORIAN,
            'font' => FontSize::FONT_16,
            'fontStyle' => 0,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'speed' => 5,
            'stay' => 10,
            'fallbackToTextMode' => false,  // New option for fallback
        ];

        $options = array_merge($defaultOptions, $options);

        // Validate options
        $this->validateClockOptions($options);

        // Apply GB2312 compatibility adjustments if enabled
        if ($this->gb2312Validation) {
            $options = $this->adjustForGB2312Compatibility($options);
        }

        // Check if we should use fallback text mode
        if ($options['fallbackToTextMode'] || $this->shouldUseFallbackMode($options)) {
            return $this->displayClockAsText($windowNo, $options);
        }

        // Store settings for this window
        $this->clockSettings[$windowNo] = $options;

        // Create and send clock packet
        $packet = PacketBuilder::createClockPacket(
            $this->controller->getConfig()['cardId'],
            $windowNo,
            $options,
        );

        $this->controller->sendPacket($packet);

        return $this;
    }

    /**
     * Display clock with predefined format presets.
     *
     * @param array<string, mixed> $overrides Override options for the preset
     */
    public function displayClockPreset(int $windowNo, string $preset, array $overrides = []): self
    {
        $presets = [
            'time_only' => [
                'content' => self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_24_HOUR,
                'font' => FontSize::FONT_16,
                'color' => Color::RED,
            ],
            'time_with_seconds' => [
                'content' => self::SHOW_HOUR | self::SHOW_MINUTE | self::SHOW_SECOND,
                'format' => self::FORMAT_24_HOUR,
                'font' => FontSize::FONT_12,
                'color' => Color::GREEN,
            ],
            'full_datetime' => [
                'content' => self::SHOW_YEAR | self::SHOW_MONTH | self::SHOW_DAY | self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_24_HOUR,
                'yearFormat' => self::YEAR_4_DIGIT,
                'rowFormat' => self::MULTI_ROW,
                'font' => FontSize::FONT_12,
                'color' => Color::BLUE,
            ],
            'date_only' => [
                'content' => self::SHOW_YEAR | self::SHOW_MONTH | self::SHOW_DAY,
                'yearFormat' => self::YEAR_4_DIGIT,
                'font' => FontSize::FONT_16,
                'color' => Color::YELLOW,
            ],
            'time_12h' => [
                'content' => self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_12_HOUR,
                'font' => FontSize::FONT_16,
                'color' => Color::MAGENTA,
                'fallbackToTextMode' => true,  // Force fallback for 12-hour format
            ],
            'compact_datetime' => [
                'content' => self::SHOW_MONTH | self::SHOW_DAY | self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_24_HOUR,
                'yearFormat' => self::YEAR_2_DIGIT,
                'font' => FontSize::FONT_12,
                'color' => Color::CYAN,
            ],
            // GB2312-safe presets
            'time_gb2312_safe' => [
                'content' => self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_24_HOUR,
                'yearFormat' => self::YEAR_4_DIGIT,
                'rowFormat' => self::SINGLE_ROW,
                'calendar' => self::CALENDAR_GREGORIAN,
                'font' => FontSize::FONT_16,
                'color' => Color::RED,
            ],
            'datetime_gb2312_safe' => [
                'content' => self::SHOW_YEAR | self::SHOW_MONTH | self::SHOW_DAY | self::SHOW_HOUR | self::SHOW_MINUTE,
                'format' => self::FORMAT_24_HOUR,
                'yearFormat' => self::YEAR_4_DIGIT,
                'rowFormat' => self::SINGLE_ROW,
                'calendar' => self::CALENDAR_GREGORIAN,
                'font' => FontSize::FONT_12,
                'color' => Color::GREEN,
            ],
        ];

        if (!isset($presets[$preset])) {
            throw new ValidationException("Unknown clock preset: {$preset}");
        }

        $options = array_merge($presets[$preset], $overrides);

        return $this->displayClock($windowNo, $options);
    }

    /**
     * Display clock as text (fallback mode for GB2312 compatibility).
     *
     * @param array<string, mixed> $options Clock display options
     */
    public function displayClockAsText(int $windowNo, array $options = []): self
    {
        // Generate clock text that's compatible with GB2312
        $clockText = $this->generateGB2312CompatibleClockText($options);

        // Display as text using the controller's text display method
        $this->controller->external()->displayText($windowNo, $clockText, [
            'font' => $options['font'] ?? FontSize::FONT_16,
            'color' => $options['color'] ?? Color::RED,
            'effect' => $options['effect'] ?? Effect::DRAW,
            'align' => $options['align'] ?? Alignment::CENTER,
            'speed' => $options['speed'] ?? 5,
            'stay' => $options['stay'] ?? 10,
        ]);

        // Store settings for this window
        $this->clockSettings[$windowNo] = array_merge($options, ['mode' => 'text_fallback']);

        return $this;
    }

    /**
     * Get controller's current time.
     */
    public function getControllerTime(): \DateTime
    {
        return $this->controller->getTime();
    }

    /**
     * Set controller's time.
     */
    public function setControllerTime(?\DateTime $dateTime = null): self
    {
        $this->controller->setTime($dateTime);

        return $this;
    }

    /**
     * Sync controller time with system time.
     */
    public function syncWithSystemTime(): self
    {
        $this->controller->setTime(new \DateTime());

        return $this;
    }

    /**
     * Get clock settings for a window.
     *
     * @param int $windowNo Window number
     *
     * @return array<string, mixed>|null Clock settings for the window
     */
    public function getClockSettings(int $windowNo): ?array
    {
        return $this->clockSettings[$windowNo] ?? null;
    }

    /**
     * Clear clock from window.
     */
    public function clearClock(int $windowNo): self
    {
        // Display empty text to clear the clock
        $this->controller->external()->displayText($windowNo, '', [
            'font' => FontSize::FONT_8,
            'color' => Color::BLACK,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
        ]);

        // Remove settings
        if (isset($this->clockSettings[$windowNo])) {
            unset($this->clockSettings[$windowNo]);
        }

        return $this;
    }

    /**
     * Format content flags from array of content types.
     *
     * @param array<int, string> $contentTypes Array of content type strings
     *
     * @return int Combined content flags
     */
    public static function formatContentFlags(array $contentTypes): int
    {
        $flags = 0;

        foreach ($contentTypes as $type) {
            $flags |= match (strtolower($type)) {
                'year' => self::SHOW_YEAR,
                'month' => self::SHOW_MONTH,
                'day' => self::SHOW_DAY,
                'hour' => self::SHOW_HOUR,
                'minute' => self::SHOW_MINUTE,
                'second' => self::SHOW_SECOND,
                'weekday' => self::SHOW_WEEKDAY,
                'pointer' => self::SHOW_POINTER,
                default => 0
            };
        }

        return $flags;
    }

    /**
     * Get human-readable format description.
     *
     * @param array<string, mixed> $options Clock display options
     */
    public function getFormatDescription(array $options): string
    {
        $descriptions = [];

        // Time format
        $descriptions[] = ($options['format'] ?? self::FORMAT_24_HOUR) === self::FORMAT_12_HOUR ? '12-hour' : '24-hour';

        // Year format
        if (($options['content'] ?? 0) & self::SHOW_YEAR) {
            $descriptions[] = ($options['yearFormat'] ?? self::YEAR_4_DIGIT) === self::YEAR_2_DIGIT ? '2-digit year' : '4-digit year';
        }

        // Row format
        $descriptions[] = ($options['rowFormat'] ?? self::SINGLE_ROW) === self::MULTI_ROW ? 'multi-row' : 'single-row';

        // Content description
        $content = $options['content'] ?? 0;
        $contentParts = [];

        if ($content & self::SHOW_YEAR) {
            $contentParts[] = 'year';
        }
        if ($content & self::SHOW_MONTH) {
            $contentParts[] = 'month';
        }
        if ($content & self::SHOW_DAY) {
            $contentParts[] = 'day';
        }
        if ($content & self::SHOW_HOUR) {
            $contentParts[] = 'hour';
        }
        if ($content & self::SHOW_MINUTE) {
            $contentParts[] = 'minute';
        }
        if ($content & self::SHOW_SECOND) {
            $contentParts[] = 'second';
        }
        if ($content & self::SHOW_WEEKDAY) {
            $contentParts[] = 'weekday';
        }

        if (!empty($contentParts)) {
            $descriptions[] = 'showing: ' . implode(', ', $contentParts);
        }

        return implode(', ', $descriptions);
    }

    /**
     * Get all available presets.
     *
     * @return array<string, string> Array of preset names and descriptions
     */
    public function getAvailablePresets(): array
    {
        return [
            'time_only' => 'Time only (HH:MM)',
            'time_with_seconds' => 'Time with seconds (HH:MM:SS)',
            'full_datetime' => 'Full date and time',
            'date_only' => 'Date only (YYYY-MM-DD)',
            'time_12h' => 'Time in 12-hour format (uses fallback)',
            'compact_datetime' => 'Compact date and time',
            'time_gb2312_safe' => 'Time only (GB2312 safe)',
            'datetime_gb2312_safe' => 'Date and time (GB2312 safe)',
        ];
    }

    /**
     * Check if current clock settings are GB2312 compatible.
     */
    public function isGB2312Compatible(int $windowNo): bool
    {
        $settings = $this->getClockSettings($windowNo);

        if (!$settings) {
            return true; // No settings means no compatibility issues
        }

        return !$this->shouldUseFallbackMode($settings);
    }

    /**
     * Get GB2312 compatibility report for clock settings.
     *
     * @param array<string, mixed> $options Clock options to analyze
     *
     * @return array<string, mixed> Compatibility report
     */
    public function getGB2312CompatibilityReport(array $options): array
    {
        $report = [
            'compatible' => true,
            'issues' => [],
            'recommendations' => [],
        ];

        // Check format compatibility
        if ($options['format'] === self::FORMAT_12_HOUR) {
            $report['compatible'] = false;
            $report['issues'][] = '12-hour format may have AM/PM indicators not in GB2312';
            $report['recommendations'][] = 'Use 24-hour format instead';
        }

        // Check calendar compatibility
        if ($options['calendar'] !== self::CALENDAR_GREGORIAN) {
            $report['compatible'] = false;
            $report['issues'][] = 'Lunar calendar modes may contain complex characters';
            $report['recommendations'][] = 'Use Gregorian calendar';
        }

        // Check time scale compatibility
        if ($options['showTimeScale']) {
            $report['compatible'] = false;
            $report['issues'][] = 'Time scale display may have unsupported characters';
            $report['recommendations'][] = 'Disable time scale display';
        }

        // Check weekday compatibility
        if ($options['content'] & self::SHOW_WEEKDAY) {
            $report['compatible'] = false;
            $report['issues'][] = 'Weekday display may have unsupported characters';
            $report['recommendations'][] = 'Remove weekday from display content';
        }

        // Check multi-row format
        if ($options['rowFormat'] === self::MULTI_ROW) {
            $report['issues'][] = 'Multi-row format may have layout issues';
            $report['recommendations'][] = 'Consider using single-row format';
        }

        return $report;
    }

    /**
     * Get GB2312 safe version of clock options.
     *
     * @param array<string, mixed> $options Original clock options
     *
     * @return array<string, mixed> GB2312-safe clock options
     */
    public function getGB2312SafeOptions(array $options): array
    {
        return $this->adjustForGB2312Compatibility($options);
    }

    /**
     * Test clock display with GB2312 compatibility.
     *
     * @param array<string, mixed> $options Clock options to test
     *
     * @return array<string, mixed> Test results
     */
    public function testGB2312Compatibility(array $options): array
    {
        $result = [
            'success' => false,
            'mode' => 'unknown',
            'message' => '',
            'options_used' => $options,
        ];

        try {
            // Apply GB2312 compatibility adjustments
            if ($this->gb2312Validation) {
                $options = $this->adjustForGB2312Compatibility($options);
            }

            // Determine which mode will be used
            if (($options['fallbackToTextMode'] ?? false) || $this->shouldUseFallbackMode($options)) {
                $result['mode'] = 'text_fallback';
                $result['message'] = 'Clock will be displayed as text (fallback mode)';

                // Test text generation
                $clockText = $this->generateGB2312CompatibleClockText($options);
                $result['preview_text'] = $clockText;
                $result['text_compatible'] = $this->validateGB2312Compatibility($clockText);
            } else {
                $result['mode'] = 'clock_packet';
                $result['message'] = 'Clock will be displayed using native clock packet';
            }

            $result['success'] = true;
            $result['options_used'] = $options;
        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Generate GB2312 compatible clock text.
     *
     * @param array<string, mixed> $options Clock display options
     */
    private function generateGB2312CompatibleClockText(array $options): string
    {
        $content = $options['content'] ?? (self::SHOW_HOUR | self::SHOW_MINUTE);
        $format = $options['format'] ?? self::FORMAT_24_HOUR;
        $yearFormat = $options['yearFormat'] ?? self::YEAR_4_DIGIT;
        $rowFormat = $options['rowFormat'] ?? self::SINGLE_ROW;

        $now = new \DateTime();
        $parts = [];

        // Build date parts
        if ($content & self::SHOW_YEAR) {
            $year = $yearFormat === self::YEAR_2_DIGIT ? $now->format('y') : $now->format('Y');
            $parts[] = $year;
        }

        if ($content & self::SHOW_MONTH) {
            $parts[] = $now->format('m');
        }

        if ($content & self::SHOW_DAY) {
            $parts[] = $now->format('d');
        }

        // Build time parts
        $timeParts = [];

        if ($content & self::SHOW_HOUR) {
            $hour = $format === self::FORMAT_12_HOUR ? $now->format('h') : $now->format('H');
            $timeParts[] = $hour;
        }

        if ($content & self::SHOW_MINUTE) {
            $timeParts[] = $now->format('i');
        }

        if ($content & self::SHOW_SECOND) {
            $timeParts[] = $now->format('s');
        }

        // Add AM/PM for 12-hour format (using ASCII characters only)
        if ($format === self::FORMAT_12_HOUR && ($content & self::SHOW_HOUR)) {
            $timeParts[] = $now->format('A');
        }

        // Join parts with GB2312-safe separators
        $datePart = implode('-', $parts);
        $timePart = implode(':', $timeParts);

        // Combine based on row format
        if ($rowFormat === self::MULTI_ROW) {
            return $datePart . "\n" . $timePart;
        }

        return trim($datePart . ' ' . $timePart);
    }

    /**
     * Adjust options for GB2312 compatibility.
     *
     * @param array<string, mixed> $options Clock display options
     *
     * @return array<string, mixed> GB2312-compatible options
     */
    private function adjustForGB2312Compatibility(array $options): array
    {
        // Force 24-hour format for better GB2312 compatibility
        if ($options['format'] === self::FORMAT_12_HOUR) {
            $options['format'] = self::FORMAT_24_HOUR;
        }

        // Prefer 4-digit year (uses only numbers)
        if ($options['yearFormat'] === self::YEAR_2_DIGIT) {
            $options['yearFormat'] = self::YEAR_4_DIGIT;
        }

        // Avoid lunar calendar modes that may have more complex characters
        if ($options['calendar'] !== self::CALENDAR_GREGORIAN) {
            $options['calendar'] = self::CALENDAR_GREGORIAN;
        }

        // Disable time scale display that may have unsupported characters
        $options['showTimeScale'] = false;

        return $options;
    }

    /**
     * Determine if fallback text mode should be used.
     *
     * @param array<string, mixed> $options Clock display options
     */
    private function shouldUseFallbackMode(array $options): bool
    {
        // Check for potentially problematic combinations
        $riskFactors = 0;

        // 12-hour format may have AM/PM indicators not in GB2312
        if ($options['format'] === self::FORMAT_12_HOUR) {
            $riskFactors++;
        }

        // Lunar calendar modes may have complex characters
        if ($options['calendar'] !== self::CALENDAR_GREGORIAN) {
            $riskFactors++;
        }

        // Multi-row format may have layout issues
        if ($options['rowFormat'] === self::MULTI_ROW) {
            $riskFactors++;
        }

        // Time scale display may have unsupported characters
        if ($options['showTimeScale']) {
            $riskFactors++;
        }

        // Weekday display may have unsupported characters
        if ($options['content'] & self::SHOW_WEEKDAY) {
            $riskFactors++;
        }

        // Use fallback if multiple risk factors present
        return $riskFactors >= 2;
    }

    /**
     * Validate GB2312 character compatibility.
     */
    private function validateGB2312Compatibility(string $text): bool
    {
        // Check if text contains only GB2312 compatible characters
        $converted = @mb_convert_encoding($text, 'GB2312', 'UTF-8');

        if ($converted === false || !is_string($converted)) {
            return false;
        }

        // Check if conversion was lossy
        $backConverted = @mb_convert_encoding($converted, 'UTF-8', 'GB2312');

        return $backConverted === $text;
    }

    /**
     * Validate clock options.
     *
     * @param array<string, mixed> $options Clock display options
     */
    private function validateClockOptions(array $options): void
    {
        // Validate window number
        // Window number will be passed separately, this is just for options validation

        // Validate format options
        if (!\in_array($options['format'] ?? self::FORMAT_24_HOUR, [self::FORMAT_12_HOUR, self::FORMAT_24_HOUR], true)) {
            throw new ValidationException('Invalid clock format');
        }

        if (!\in_array($options['yearFormat'] ?? self::YEAR_4_DIGIT, [self::YEAR_2_DIGIT, self::YEAR_4_DIGIT], true)) {
            throw new ValidationException('Invalid year format');
        }

        if (!\in_array($options['rowFormat'] ?? self::SINGLE_ROW, [self::SINGLE_ROW, self::MULTI_ROW], true)) {
            throw new ValidationException('Invalid row format');
        }

        if (
            !\in_array(
                $options['calendar'] ?? self::CALENDAR_GREGORIAN,
                [
                    self::CALENDAR_GREGORIAN,
                    self::CALENDAR_LUNAR,
                    self::CALENDAR_LUNAR_SOLAR,
                    self::CALENDAR_LUNAR_SOLAR_TERMS,
                ],
                true,
            )
        ) {
            throw new ValidationException('Invalid calendar type');
        }

        // Validate content flags
        $content = $options['content'] ?? 0;
        if ($content < 0 || $content > 0xFF) {
            throw new ValidationException('Invalid content flags');
        }

        // Validate speed and stay time
        if (isset($options['speed']) && ($options['speed'] < 1 || $options['speed'] > 100)) {
            throw new ValidationException('Speed must be between 1 and 100');
        }

        if (isset($options['stay']) && ($options['stay'] < 0 || $options['stay'] > 65535)) {
            throw new ValidationException('Stay time must be between 0 and 65535 seconds');
        }
    }
}
