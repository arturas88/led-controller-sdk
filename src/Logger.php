<?php

declare(strict_types=1);

namespace LEDController;

use LEDController\Exception\LoggerException;

/**
 * Logger for the LED Controller SDK.
 */
class Logger
{
    // Log levels
    public const DEBUG = 0;
    public const INFO = 1;
    public const WARNING = 2;
    public const ERROR = 3;
    public const CRITICAL = 4;

    private const LEVEL_NAMES = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
    ];

    private string $logFile;

    private string $logLevel;

    private static ?Logger $instance = null;

    public function __construct(?string $logFile = null, string $logLevel = 'info')
    {
        $this->logFile = $logFile ?? $this->getDefaultLogPath();
        $this->logLevel = $logLevel;
        $this->ensureLogDirectory();
    }

    /**
     * Get singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Log debug message.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log info message.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log warning message.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log error message.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log critical message.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log protocol communication.
     *
     * @param array<string, mixed> $context Additional context data
     */
    public function protocol(string $direction, string $data, array $context = []): void
    {
        $context['protocol'] = [
            'direction' => $direction,
            'data' => bin2hex($data),
            'size' => \strlen($data),
        ];

        $this->debug("Protocol {$direction}: " . bin2hex($data), $context);
    }

    /**
     * Get default log file path.
     */
    private function getDefaultLogPath(): string
    {
        $logDir = \dirname(__DIR__, 2) . '/logs';

        return $logDir . '/led-controller-' . date('Y-m-d') . '.log';
    }

    /**
     * Ensure log directory exists.
     */
    private function ensureLogDirectory(): void
    {
        $logDir = \dirname($this->logFile);
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0o755, true)) {
                throw new LoggerException("Failed to create log directory: {$logDir}");
            }
        }
    }

    /**
     * Log message with level.
     *
     * @param array<string, mixed> $context Additional context data
     */
    private function log(int $level, string $message, array $context = []): void
    {
        $logEntry = $this->formatLogEntry($level, $message, $context);
        $this->writeLog($logEntry);
    }

    /**
     * Format log entry.
     *
     * @param array<string, mixed> $context Additional context data
     */
    private function formatLogEntry(int $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = self::LEVEL_NAMES[$level] ?? 'UNKNOWN';

        $entry = "[{$timestamp}] [{$levelName}] {$message}";

        if (!empty($context)) {
            $entry .= ' ' . json_encode($context);
        }

        return $entry . "\n";
    }

    /**
     * Write log to file.
     */
    private function writeLog(string $logEntry): void
    {
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
