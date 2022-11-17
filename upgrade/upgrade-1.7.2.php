<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_7_2(MyParcelBE $module): bool
{
    return $module->upgrade(Gett\MyparcelBE\Module\Upgrade\Upgrade1_7_2::class);
}
