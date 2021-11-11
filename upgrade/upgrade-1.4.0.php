<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_4_0(MyParcelBE $module): bool
{
    $carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);

    $query = <<<SQL
ALTER TABLE $carrierConfigurationTable ALTER COLUMN type text;
SQL;

    Db::getInstance()->execute($query);

    return true;
}
