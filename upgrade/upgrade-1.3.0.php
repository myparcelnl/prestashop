<?php

use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Database\Table;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_3_0(MyParcelNL $module): bool
{
    $deliverySettingsTable = Table::withPrefix(Table::TABLE_DELIVERY_SETTINGS);

    $query = <<<SQL
alter table $deliverySettingsTable add column extra_options text;
SQL;

    Db::getInstance()->execute($query);

    return true;
}
