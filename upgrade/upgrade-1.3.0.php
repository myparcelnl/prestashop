<?php

use Gett\MyparcelBE\Database\Table;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_3_0(): bool
{
    $deliverySettingsTable = Table::withPrefix(Table::TABLE_DELIVERY_SETTINGS);
    $carrierConfiguration  = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);

    $extraOptions = <<<SQL
ALTER TABLE $deliverySettingsTable ADD COLUMN extra_options text;
SQL;

    $carrierConfig = <<<SQL
ALTER TABLE $carrierConfiguration ALTER COLUMN type text NOT NULL;
SQL;

    Db::getInstance()->execute($extraOptions);
    Db::getInstance()->execute($carrierConfig);

    return true;
}
