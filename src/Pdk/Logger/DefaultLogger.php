<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Logger;

use FileLogger;
use MyParcelBE;
use MyParcelNL\Pdk\Logger\AbstractLogger;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
class DefaultLogger extends AbstractLogger
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
     * @var \FileLogger
     */
    private static $loggers = [];

    public function __construct()
    {
        $directory = $this->getLogDirectory();

        $this->createLogDirectory($directory);
    }

    /**
     * @param  string $directory
     *
     * @return void
     */
    public function createLogDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        if (_PS_MODE_DEV_) {
            foreach (self::LOG_LEVELS as $level) {
                // Create all log files in advance on dev for easier tail usage
                $handle = fopen($this->getLogFilename($level), 'wb');
                fclose($handle);
            }
        }
    }

    /**
     * @param        $level
     * @param        $message
     * @param  array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $logger = $this->getLogger($level);
        $string = $this->createMessage($message, $context, $level);

        $logger->log($string);
    }

    /**
     * @param  \Throwable|array|string $message
     * @param  array                   $context
     * @param  string                  $level
     *
     * @return void
     */
    protected function createMessage($message, array $context, string $level): string
    {
        $output     = $this->getOutput($message);
        $logContext = Arr::except($context, 'exception');

        if (! empty($logContext)) {
            $output .= "\nContext: " . json_encode($logContext, JSON_PRETTY_PRINT);
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

    private function getLogDirectory(): string
    {
        return sprintf('%s/var/logs/%s', _PS_ROOT_DIR_, MyParcelBE::MODULE_NAME);
    }

    /**
     * @param $level
     *
     * @return string
     */
    private function getLogFilename($level): string
    {
        return sprintf("%s/%s.log", $this->getLogDirectory(), $level);
    }

    /**
     * @param $level
     *
     * @return \FileLogger
     */
    private function getLogger($level): FileLogger
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
        $throwable = $context['exception'];

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
     * @param $level
     *
     * @return \FileLogger
     */
    private function initializeLogger($level): FileLogger
    {
        $logger = new FileLogger($level);
        $logger->setFilename($this->getLogFilename($level));

        return $logger;
    }
}
