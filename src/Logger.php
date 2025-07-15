<?php

namespace LEDController;

use LEDController\Exception\LoggerException;

/**
 * Logger for the LED Controller SDK
 */
class Logger
{
    private string $logFile;
    private string $logLevel;
    private static ?Logger $instance = null;

    // Log levels
    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRITICAL = 4;

    private const LEVEL_NAMES = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL'
    ];

    public function __construct(?string $logFile = null, string $logLevel = 'info')
    {
        $this->logFile = $logFile ?? $this->getDefaultLogPath();
        $this->logLevel = $logLevel;
        $this->ensureLogDirectory();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get default log file path
     */
    private function getDefaultLogPath(): string
    {
        $logDir = dirname(__DIR__, 2) . '/logs';
        return $logDir . '/led-controller-' . date('Y-m-d') . '.log';
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new LoggerException("Failed to create log directory: $logDir");
            }
        }
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log protocol communication
     */
    public function protocol(string $direction, string $data, array $context = []): void
    {
        $context['protocol'] = [
            'direction' => $direction,
            'data' => bin2hex($data),
            'size' => strlen($data)
        ];

        $this->debug("Protocol {$direction}: " . bin2hex($data), $context);
    }

    /**
     * Log message with level
     */
    private function log(int $level, string $message, array $context = []): void
    {
        $logEntry = $this->formatLogEntry($level, $message, $context);
        $this->writeLog($logEntry);
    }

    /**
     * Format log entry
     */
    private function formatLogEntry(int $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = self::LEVEL_NAMES[$level] ?? 'UNKNOWN';

        $entry = "[$timestamp] [$levelName] $message";

        if (!empty($context)) {
            $entry .= " " . json_encode($context);
        }

        return $entry . "\n";
    }

    /**
     * Write log to file
     */
    private function writeLog(string $logEntry): void
    {
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
