<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use DbQuery;

final class Migration1_1_2 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.1.2';
    }

    /**
     * @return void
     */
    public function up(): void
    {
        $query = new DbQuery();

        $query
            ->select('carrier.*')
            ->from('carrier', 'carrier')
            ->leftJoin(
                self::LEGACY_TABLE_CARRIER_CONFIGURATION,
                'config',
                "`carrier.id_carrier` = `config.id_carrier` AND `config.name` = 'carrierType'"
            )
            ->where('`carrier.external_module_name` = \'myparcelnl\'')
            ->where('`config.id_configuration` IS NULL');

        $records = $this->getRows($query);

        $newRecords = [];

        foreach ($records as $record) {
            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'carrierType',
                'value'      => $this->getCarrierType($record['name'] ?? ''),
            ];
        }

        $this->insertRows(self::LEGACY_TABLE_CARRIER_CONFIGURATION, $newRecords);
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
