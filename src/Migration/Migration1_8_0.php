<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use DbQuery;

final class Migration1_8_0 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.8.0';
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->addInsuranceOptionsEuForCarriers();
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceOptionsEuForCarriers(): void
    {
        $table   = $this->getCarrierConfigurationTable();
        $records = $this->getAll($table, function (DbQuery $query) {
            $query->where("`name` LIKE '%MYPARCELBE_INSURANCE'");
        });

        $newRecords = [];

        foreach ($records as $record) {
            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'MYPARCELBE_INSURANCE_FROM_PRICE_EU',
                'value'      => '0',
            ];

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'MYPARCELBE_INSURANCE_MAX_AMOUNT_EU',
                'value'      => '0',
            ];
        }

        $this->insert($table, $newRecords);
    }
}
