<?php

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_8_0(MyParcelBE $module): bool
{
    return $module->upgrade(Gett\MyparcelBE\Module\Upgrade\Upgrade1_8_0::class);
}
