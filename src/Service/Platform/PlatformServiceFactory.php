<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use Exception;
use MyParcelBE;

class PlatformServiceFactory
{
    /**
     * @throws \Exception
     */
    public static function create(): AbstractPlatformService
    {
        $module = MyParcelBE::getModule();

        if ($module->isNL()) {
            return MyParcelPlatformService::getInstance();
        }

        if ($module->isBE()) {
            return SendMyParcelPlatformService::getInstance();
        }

        throw new Exception('Could not determine platform.');
    }
}
