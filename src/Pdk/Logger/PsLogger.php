<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Logger;

use FileLogger;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\AbstractLogger;
use MyParcelNL\Sdk\src\Support\Arr;
use Psr\Log\LogLevel;

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
     * @var \FileLogger[]
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
        $logger      = $this->getLogger($level);
        $fullMessage = empty($context)
            ? $message
            : sprintf('%s %s', $message, json_encode(Utils::filterNull($context)));

        $logger->log($fullMessage, $this->mapLevel($level));
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
            $this->createLogFile($level);
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
     * @return \FileLogger
     */
    private function getLogger(string $level): FileLogger
    {
        if (! isset(self::$loggers[$level])) {
            self::$loggers[$level] = $this->initializeLogger($level);
        }

        return self::$loggers[$level];
    }

    /**
     * @param  string $level
     *
     * @return \FileLogger
     */
    private function initializeLogger(string $level): FileLogger
    {
        $this->createLogFile($level);

        $logger = new FileLogger($this->mapLevel($level));
        $logger->setFilename($this->getLogFilename($level));

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
}
