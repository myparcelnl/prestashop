<?php

declare(strict_types=1);

defined('_PS_VERSION_') or die();

function upgrade_module_2_0_0(MyParcelNL $module): bool
{
    return $module->upgrade(MyParcelNL\PrestaShop\Module\Upgrade\Upgrade2_0_0::class);
}
