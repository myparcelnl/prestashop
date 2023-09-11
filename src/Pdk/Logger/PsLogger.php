<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Logger;

use FileLogger;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\AbstractLogger;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
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
     * Map of PSR-3 log levels to FileLogger log levels.
     */
    private const LEVEL_MAP = [
        LogLevel::DEBUG     => FileLogger::DEBUG,
        LogLevel::INFO      => FileLogger::INFO,
        LogLevel::NOTICE    => FileLogger::WARNING,
        LogLevel::WARNING   => FileLogger::WARNING,
        LogLevel::ERROR     => FileLogger::ERROR,
        LogLevel::CRITICAL  => FileLogger::ERROR,
        LogLevel::ALERT     => FileLogger::ERROR,
        LogLevel::EMERGENCY => FileLogger::ERROR,
    ];

    /**
     * @var \FileLogger
     */
    private static $loggers = [];

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private $fileSystem;

    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;

        $this->createLogDirectory();
    }

    /**
     * @param  string                  $level
     * @param  \Throwable|array|string $message
     * @param  array                   $context
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        if (! is_string($level) || ! in_array($level, self::LOG_LEVELS, true)) {
            throw new InvalidArgumentException(sprintf('Invalid log level "%s"', $level));
        }

        $logger = $this->getLogger($level);
        $string = $this->createMessage($message, $context, $level);

        $logger->log($string, $this->mapLevel($level));
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

        if (! $this->fileSystem->isDir(Pdk::get('logDirectory'))) {
            $this->fileSystem->mkdir(Pdk::get('logDirectory'), true);
        }

        if (! $this->fileSystem->fileExists($file)) {
            $this->fileSystem->put($file, '');
        }
    }

    /**
     * @param  \Throwable|array|string $message
     * @param  array                   $context
     * @param  string                  $level
     *
     * @return void
     */
    private function createMessage($message, array $context, string $level): string
    {
        $output     = $this->getOutput($message);
        $logContext = Arr::except($context, 'exception');

        if (! empty($logContext)) {
            $output .= "\n" . json_encode($logContext, JSON_PRETTY_PRINT);
        }

        if (LogLevel::DEBUG !== $level) {
            $output .= $this->getSource($context);
        }

        return $output;
    }

    /**
     * Get the first caller that's not a *Logger.php file.
     *
     * @return null|array
     */
    private function getCaller(): ?array
    {
        $backtrace = debug_backtrace();
        $caller    = current(
            array_filter(
                $backtrace,
                static function ($item) {
                    return isset($item['file'])
                        && ! Str::endsWith($item['file'], 'Logger.php')
                        && ! Str::contains($item['file'], 'Facade.php');
                }
            )
        );

        if (! $caller) {
            $caller = null;
        }

        return $caller;
    }

    /**
     * @param  string $level
     *
     * @return string
     */
    private function getLogFilename(string $level): string
    {
        return sprintf('%s/%s.log', Pdk::get('logDirectory'), $level);
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
     * @param  \Throwable|array|string $message
     *
     * @return string
     */
    private function getOutput($message): string
    {
        $output = $message;

        if ($message instanceof Throwable) {
            $output = $message->getMessage();
        } elseif (! is_string($message)) {
            $output = (string) json_encode($message, JSON_PRETTY_PRINT);
        }

        return $output;
    }

    /**
     * @param  array $context
     *
     * @return string
     */
    private function getSource(array $context): string
    {
        $throwable = $context['exception'] ?? null;

        if ($throwable instanceof Throwable) {
            if (_PS_MODE_DEV_) {
                return sprintf(
                    "\nMessage: %s\nStack trace: %s",
                    $throwable->getMessage(),
                    $throwable->getTraceAsString()
                );
            }

            $file = $throwable->getFile();
            $line = $throwable->getLine();
        } else {
            $caller = $this->getCaller();
            $file   = $caller['file'];
            $line   = $caller['line'];
        }

        return sprintf(' (%s:%s)', $file, $line);
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
        return self::LEVEL_MAP[$level];
    }
}
