<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Logger;

use FileLogger;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\AbstractLogger;
use MyParcelNL\Sdk\Support\Arr;
use PrestaShopLogger;
use Psr\Log\LogLevel;
use Throwable;

final class PsLogger extends AbstractLogger
{
    /**
     * Log levels, in order of severity.
     */
    private const LOG_LEVELS = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY,
    ];

    /**
     * @var array<string, null|\FileLogger>
     */
    private static array $loggers = [];

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private FileSystemInterface $fileSystem;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;

        $this->createLogDirectory();
    }

    /**
     * @return string[]
     */
    public function getLogFiles(): array
    {
        return array_map(function (string $level): string {
            return $this->getLogFilename($level);
        }, self::LOG_LEVELS);
    }

    /**
     * @param  string                  $level
     * @param  \Throwable|array|string $message
     * @param  array                   $context
     *
     * @return void
     * @noinspection JsonEncodingApiUsageInspection
     */
    public function log($level, $message, array $context = []): void
    {
        $fullMessage = empty($context)
            ? $message
            : sprintf('%s %s', $message, json_encode(Utils::filterNull($context)));

        $logger = $this->getLogger($level);

        if (! $logger) {
            $this->logToPrestaShop($level, $fullMessage);

            return;
        }

        try {
            $logger->log($fullMessage, $this->mapLevel($level));
        } catch (Throwable $e) {
            $this->logToPrestaShop(
                $level,
                sprintf('%s Failed to write MyParcel log: %s', $fullMessage, $e->getMessage())
            );
        }
    }

    /**
     * @return void
     */
    private function createLogDirectory(): void
    {
        if (Pdk::isProduction()) {
            return;
        }

        // Create all log files in advance on dev for easier tail usage
        foreach (self::LOG_LEVELS as $level) {
            try {
                $this->createLogFile($level);
            } catch (Throwable $e) {
                $this->logToPrestaShop(
                    LogLevel::WARNING,
                    sprintf('Failed to prepare MyParcel log file for %s: %s', $level, $e->getMessage())
                );
            }
        }
    }

    /**
     * @param  string $level
     *
     * @return void
     */
    private function createLogFile(string $level): void
    {
        $file = $this->getLogFilename($level);

        $this->fileSystem->mkdir(Pdk::get('logDirectory'), true);

        if (! $this->fileSystem->fileExists($file)) {
            $this->fileSystem->put($file, '');
        }
    }

    /**
     * @param  string $level
     *
     * @return string
     */
    private function getLogFilename(string $level): string
    {
        return preg_replace('/\/+/', '/', sprintf('%s/%s.log', Pdk::get('logDirectory'), $level));
    }

    /**
     * @param  string $level
     *
     * @return null|\FileLogger
     */
    private function getLogger(string $level): ?FileLogger
    {
        if (! array_key_exists($level, self::$loggers)) {
            self::$loggers[$level] = $this->initializeLogger($level);
        }

        return self::$loggers[$level];
    }

    /**
     * @param  string $level
     *
     * @return null|\FileLogger
     */
    private function initializeLogger(string $level): ?FileLogger
    {
        try {
            $this->createLogFile($level);
        } catch (Throwable $e) {
            $this->logToPrestaShop(
                LogLevel::WARNING,
                sprintf('Failed to prepare MyParcel log file for %s: %s', $level, $e->getMessage())
            );

            return null;
        }

        $file = $this->getLogFilename($level);

        if (! is_writable(dirname($file))) {
            $this->logToPrestaShop(
                LogLevel::WARNING,
                sprintf('MyParcel log directory is not writable: %s', dirname($file))
            );

            return null;
        }

        $logger = new FileLogger($this->mapLevel($level));
        $logger->setFilename($file);

        return $logger;
    }

    /**
     * @param  string $level
     *
     * @return int
     */
    private function mapLevel(string $level): int
    {
        return Arr::get(Pdk::get('logLevelFilenameMap'), $level);
    }

    /**
     * @param  string $level
     * @param  mixed  $message
     *
     * @return void
     */
    private function logToPrestaShop(string $level, $message): void
    {
        if (! class_exists(PrestaShopLogger::class)) {
            return;
        }

        try {
            PrestaShopLogger::addLog((string) $message, $this->mapPrestaShopLevel($level));
        } catch (Throwable $e) {
            // Logging should never interrupt module install/update flows.
        }
    }

    /**
     * @param  string $level
     *
     * @return int
     */
    private function mapPrestaShopLevel(string $level): int
    {
        if (LogLevel::WARNING === $level) {
            return PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING;
        }

        if (LogLevel::ERROR === $level) {
            return PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR;
        }

        if (in_array($level, [LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY], true)) {
            return PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR;
        }

        return PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE;
    }
}
