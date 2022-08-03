<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use Gett\MyparcelBE\Module\Facade\ModuleService;

/**
 * @deprecated
 */
class PlatformServiceFactory
{
    /**
     * @return \Gett\MyparcelBE\Service\Platform\AbstractPlatformService
     * @throws \PrestaShopBundle\Exception\InvalidModuleException
     * @throws \Exception
     */
    public static function create(): AbstractPlatformService
    {
        if (ModuleService::isNL()) {
            return MyParcelPlatformService::getInstance();
        }

        if (ModuleService::isBE()) {
            return SendMyParcelPlatformService::getInstance();
        }

        throw new \RuntimeException('Could not determine platform.');
    }
}
