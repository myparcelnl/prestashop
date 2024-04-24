<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Facade\MyParcelModule;

/**
 * @see \MyParcelNL\PrestaShop\Pdk\Installer\Service\PsInstallerService::install
 */
function upgrade_module_1_6_0($module): bool
{
    return MyParcelModule::install($module);
}
