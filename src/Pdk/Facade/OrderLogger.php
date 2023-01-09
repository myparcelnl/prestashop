<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static void log($level, $message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @implements \MyParcelNL\PrestaShop\Pdk\Logger\OrderLogger
 */
class OrderLogger extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return \MyParcelNL\PrestaShop\Pdk\Logger\OrderLogger::class;
    }
}