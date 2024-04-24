<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Facade\MyParcelModule;

/**
 * The PDK upgrade.
 *
 * @see \MyParcelNL\PrestaShop\Pdk\Installer\Service\PsInstallerService::install
 */
function upgrade_module_4_0_0($module): bool
{
    return MyParcelModule::install($module);
}
