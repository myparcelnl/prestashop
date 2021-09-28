<?php

namespace Gett\MyparcelBE\Logger;

use Configuration;
use Gett\MyparcelBE\Constant;
use MyParcelBE;
use PrestaShopLogger;

class Logger
{
    /**
     * @param  string $message
     * @param  bool   $isException
     * @param  false  $allowDuplicate
     * @param  int    $severity
     * @param  null   $errorCode
     */
    public static function addLog(
        string $message,
        bool   $isException = false,
        bool   $allowDuplicate = false,
        int    $severity = 1,
               $errorCode = null
    ): void {
        if ($isException || Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            $bt     = debug_backtrace();
            $caller = array_shift($bt);

            PrestaShopLogger::addLog(
                sprintf('[MYPARCEL | %s:%s] %s', $caller['file'], $caller['line'], $message),
                $severity,
                $errorCode,
                MyParcelBE::MODULE_NAME,
                null,
                $allowDuplicate
            );
        }
    }
}
