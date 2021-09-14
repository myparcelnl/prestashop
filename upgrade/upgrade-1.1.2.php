<?php

use Gett\MyparcelBE\Database\Table;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_1_2(MyParcelBE $module): bool
{
    $carrier              = Table::withPrefix('carrier');
    $carrierConfiguration = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);

    $query = <<<SQL
SELECT carrier.* FROM $carrier AS carrier
  LEFT JOIN $carrierConfiguration 
    AS config 
    ON carrier.id_carrier = config.id_carrier AND config.name = carriertype
WHERE carrier.external_module_name = {$module::MODULE_NAME}
AND config.id_configuration IS NULL
SQL;

    foreach (Db::getInstance()
                 ->executeS($query) as $record) {
        if (preg_match('/Post\s?NL/', $record)) {
            $carrierType = \Gett\MyparcelBE\Constant::POSTNL_CARRIER_NAME;
        } elseif (strpos($record, 'DPD')) {
            $carrierType = \Gett\MyparcelBE\Constant::DPD_CARRIER_NAME;
        } else {
            $carrierType = $module->isNL()
                ? \Gett\MyparcelBE\Constant::POSTNL_CARRIER_NAME
                : \Gett\MyparcelBE\Constant::BPOST_CARRIER_NAME;
        }

        Db::getInstance()
            ->insert(Table::TABLE_CARRIER_CONFIGURATION, [
                [
                    'id_carrier' => (int) $record['id_carrier'],
                    'name'       => 'carrierType',
                    'value'      => $carrierType,
                ],
            ]);
    }

    return true;
}
