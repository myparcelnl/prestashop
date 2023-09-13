<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Migration;

use DbQuery;

final class Migration1_6_0 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.6.0';
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->addInsuranceOptionsForCarriers();
        $this->addInsuranceBelgiumForCarriers();
    }

    /**
     * For all carriers in PrestaShop adds the 'insurance Belgium' setting and sets it to true.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceBelgiumForCarriers(): void
    {
        $table   = $this->getCarrierConfigurationTable();
        $records = $this->getRows("SELECT DISTINCT `id_carrier` FROM `$table`");

        $newRecords = [];

        foreach ($records as $record) {
            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'MYPARCELNL_INSURANCE_BELGIUM',
                'value'      => '1',
            ];

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'return_MYPARCELNL_INSURANCE_BELGIUM',
                'value'      => '1',
            ];
        }

        $this->insert($table, $newRecords);
    }

    /**
     * Adds insurance_from_price and insurance_max_amount to carrier configuration if not already present
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceOptionsForCarriers(): void
    {
        $table   = $this->getCarrierConfigurationTable();
        $records = $this->getAll($table, function (DbQuery $query) {
            $query->where('name like %MYPARCELNL_INSURANCE_');
        });

        $newRecords = [];

        foreach ($records as $record) {
            $maxAmount = '1' === $record['value'] ? 500 : 0;

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'MYPARCELNL_INSURANCE_FROM_PRICE',
                'value'      => '0',
            ];

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'return_MYPARCELNL_INSURANCE_FROM_PRICE',
                'value'      => '0',
            ];

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'MYPARCELNL_INSURANCE_MAX_AMOUNT',
                'value'      => (string) $maxAmount,
            ];

            $newRecords[] = [
                'id_carrier' => (int) $record['id_carrier'],
                'name'       => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT',
                'value'      => (string) $maxAmount,
            ];
        }

        $this->insert($table, $newRecords);
    }
}
