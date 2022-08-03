<?php

declare(strict_types=1);

defined('_PS_VERSION_') or die();

function upgrade_module_2_0_0(MyParcelBE $module): bool
{
    return $module->upgrade(Gett\MyparcelBE\Module\Upgrade\Upgrade2_0_0::class);
}
