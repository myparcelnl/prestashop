<?php

declare(strict_types=1);

defined('_PS_VERSION_') or die();

function upgrade_module_1_6_0(MyParcelBE $module): bool
{
    return $module->upgrade(Gett\MyparcelBE\Module\Upgrade\Upgrade1_6_0::class);
}
