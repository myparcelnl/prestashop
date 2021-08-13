<?php

namespace Gett\MyparcelBE\Logger;

use Configuration;
use Gett\MyparcelBE\Constant;

/**
 * Only logs data if API Logging is enabled in the MyParcel settings.
 */
class ApiLogger extends Logger
{
    /**
     * @param        $message
     * @param  bool  $is_exception
     * @param  false $allowDuplicate
     * @param  int   $severity
     * @param  null  $errorCode
     */
    public static function addLog(
        $message,
        bool $is_exception = false,
        $allowDuplicate = false,
        $severity = 1,
        $errorCode = null
    ) {
        if (Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            parent::addLog($message, $is_exception, $allowDuplicate, $severity, $errorCode);
        }
    }
}
