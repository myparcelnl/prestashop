<?php

declare(strict_types=1);

defined('_PS_VERSION_') or die();

function upgrade_module_1_6_0(MyParcelNL $module): bool
{
    return $module->upgrade(MyParcelNL\PrestaShop\Module\Upgrade\Upgrade1_6_0::class);
}
