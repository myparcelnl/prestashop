<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Logger;

use Exception;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use MyParcelBE;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Adapter\Entity\AbstractLogger;
use PrestaShop\PrestaShop\Adapter\Entity\FileLogger as PrestaShopFileLogger;

class FileLogger extends PrestaShopFileLogger
{
    use HasInstance;

    /**
     * @param  \Exception|array|string $message
     * @param  int                     $level
     *
     * @return void
     */
    public static function addLog(
        $message,
        int $level = AbstractLogger::DEBUG
    ): void {
        $logger = self::getInstance(AbstractLogger::DEBUG);
        $logger->setFilename(
            sprintf(
                '%s/var/logs/%s.log',
                _PS_ROOT_DIR_,
                MyParcelBE::MODULE_NAME
            )
        );

        $string = self::createMessage($level, $message);

        $logger->log($string, $level);
    }

    /**
     * @param  \Exception|array|string $message
     *
     * @return void
     */
    private static function createMessage(int $level, $message): string
    {
        $output = $message;

        if (is_a($message, Exception::class)) {
            $output = $message->getMessage();
            $source = sprintf('%s:%s', $message->getFile(), $message->getLine());
        } else {
            $caller = self::getCaller();
            $source = sprintf('%s:%s]', $caller['file'], $caller['line']);
        }

        if (! is_string($message)) {
            $output = json_encode($message, JSON_PRETTY_PRINT);
        }

        if ($level === self::ERROR) {
            return "$output\nSource: $source";
        }

        return $output;
    }

    /**
     * Get the first caller that's not a *Logger.php file.
     *
     * @return null|array
     */
    private static function getCaller(): ?array
    {
        $backtrace = debug_backtrace();
        $caller    = current(
            array_filter(
                $backtrace,
                static function ($item) {
                    return isset($item['file']) && ! Str::endsWith($item['file'], 'Logger.php');
                }
            )
        );

        if (! $caller) {
            $caller = null;
        }

        return $caller;
    }
}
