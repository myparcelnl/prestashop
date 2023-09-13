<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Migration;

final class Migration1_1_2 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.1.2';
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $table      = $this->getCarrierConfigurationTable();
        $moduleName = 'myparcelnl';

        $query = <<<SQL
SELECT `carrier.*` FROM `carrier` AS `carrier`
  LEFT JOIN `$table` 
    AS config 
    ON `carrier.id_carrier` = `config.id_carrier` AND `config.name` = 'carrierType'
WHERE `carrier.external_module_name` = `$moduleName`
AND `config.id_configuration` IS NULL
SQL;

        $records = $this->getRows($query);

        $newRecords = [];

        foreach ($records as $record) {
            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'carrierType',
                'value'      => $this->getCarrierType($record['name'] ?? ''),
            ];
        }

        $this->insert($table, $newRecords);
    }

    /**
     * @param  string $name
     *
     * @return string
     */
    private function getCarrierType(string $name): string
    {
        if (stripos($name, 'bpost')) {
            return 'bpost';
        }

        if (stripos($name, 'dpd')) {
            return 'dpd';
        }

        return 'postnl';
    }
}
