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
ALTER TABLE $carrierConfigurationTable MODIFY value TEXT;
SQL;

    Db::getInstance()->execute($query);

    return true;
}
