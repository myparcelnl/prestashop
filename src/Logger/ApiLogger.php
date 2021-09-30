<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Logger;

use Configuration;
use Gett\MyparcelBE\Constant;

/**
 * Only logs data if API Logging is enabled in the MyParcel settings.
 */
class ApiLogger extends FileLogger
{
    /**
     * @param  mixed $message
     * @param  int   $level
     */
    public static function addLog(
        $message,
        int $level = self::DEBUG
    ): void {
        if (! Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            return;
        }

        parent::addLog($message, $level);

        // Also log to the DB for the time being.
        Logger::addLog($message, $level > 0, true, $level);
    }
}
