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
        $string = self::createMessage($message, $level);

        self::getLogger()
            ->log($string, $level);
    }

    /**
     * @param  \Exception|array|string $message
     *
     * @return string
     */
    public static function getOutput($message): string
    {
        $output = $message;

        if (is_a($message, Exception::class)) {
            $output = $message->getMessage();
        } elseif (! is_string($message)) {
            $output = (string) json_encode($message, JSON_PRETTY_PRINT);
        }

        return $output;
    }

    /**
     * @param  \Exception|array|string $message
     *
     * @return string
     */
    public static function getSource($message): string
    {
        if ($message instanceof Exception) {
            $file = $message->getFile();
            $line = $message->getLine();
        } else {
            $caller = self::getCaller();
            $file   = $caller['file'];
            $line   = $caller['line'];
        }

        return sprintf('%s:%s', $file, $line);
    }

    /**
     * @param  \Exception|array|string $message
     * @param  int                     $level
     *
     * @return void
     */
    protected static function createMessage($message, int $level): string
    {
        $output = self::getOutput($message);
        $source = self::getSource($message);

        if ($level > self::DEBUG) {
            return "$output ($source)";
        }

        return $output;
    }

    /**
     * @return \Gett\MyparcelBE\Logger\FileLogger
     */
    protected static function getLogger(): FileLogger
    {
        $logger = self::getInstance(AbstractLogger::DEBUG);
        $logger->setFilename(
            sprintf(
                '%s/var/logs/%s.log',
                _PS_ROOT_DIR_,
                MyParcelBE::MODULE_NAME
            )
        );
        return $logger;
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
