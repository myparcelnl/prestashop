<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Pdk\Base\Service\CountryService;

/**
 * @throws \PrestaShopDatabaseException
 */
function upgrade_module_1_1_2(MyParcelBE $module): bool
{
    $carrier              = Table::withPrefix('carrier');
    $carrierConfiguration = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
    $moduleName           = $module::MODULE_NAME;

    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $countryService */
    $countryService = $module->get(CountryService::class);

    $query = <<<SQL
SELECT carrier.* FROM $carrier AS carrier
  LEFT JOIN $carrierConfiguration 
    AS config 
    ON carrier.id_carrier = config.id_carrier AND config.name = 'carrierType'
WHERE carrier.external_module_name = '$moduleName'
AND config.id_configuration IS NULL
SQL;

    foreach (Db::getInstance()
                 ->executeS($query) as $record) {
        if (preg_match('/Post\s?NL/', $record['name'])) {
            $carrierType = Constant::POSTNL_CARRIER_NAME;
        } elseif (strpos($record['name'], 'DPD')) {
            $carrierType = Constant::DPD_CARRIER_NAME;
        } else {
            $carrierType = $countryService->isNL()
                ? Constant::POSTNL_CARRIER_NAME
                : Constant::BPOST_CARRIER_NAME;
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
