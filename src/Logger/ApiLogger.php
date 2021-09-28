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
     * @param  bool  $isException
     * @param  false $allowDuplicate
     * @param  int   $severity
     * @param  null  $errorCode
     */
    public static function addLog(
             $message,
        bool $isException = false,
        bool $allowDuplicate = false,
        int  $severity = 1,
             $errorCode = null
    ) {
        if (Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            parent::addLog($message, $isException, $allowDuplicate, $severity, $errorCode);
        }
    }
}
