<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Logger;

use Configuration;
use MyParcelNL\PrestaShop\Constant;

/**
 * Only logs data if API Logging is enabled in the MyParcel settings.
 *
 * @deprecated
 */
class DeprecatedApiLogger extends DeprecatedFileLogger
{
    /**
     * @param  \Throwable|array|string $message
     * @param  int                     $level
     */
    public static function addLog(
        $message,
        int $level = self::DEBUG
    ): void {
        if (! Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            return;
        }

        parent::addLog($message, $level);
    }
}
