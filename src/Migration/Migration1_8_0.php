<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Database\Table;

final class Migration1_8_0 extends AbstractPsMigration
{
    public function down(): void
    {
        // do nothing
    }

    public function getVersion(): string
    {
        return '1.8.0';
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->addInsuranceOptionsEuForCarriers();
    }

    /**
     * Adds insurance_from_price and insurance_max_amount to carrier configuration if not already present
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceOptionsEuForCarriers(): void
    {
        $table      = Table::withPrefix('myparcelnl_carrier_configuration');
        $configName = 'MYPARCELBE_INSURANCE';

        $query = <<<SQL
SELECT DISTINCT `id_carrier`, `name`, `value` FROM `$table` WHERE name LIKE `%$configName`
SQL;

        $records = new Collection($this->db->executeS($query));

        foreach ($records as $record) {
            $carrierId = (int) $record['id_carrier'];

            $this->insertOption($carrierId, 'MYPARCELBE_INSURANCE_MAX_AMOUNT_EU', '0');
            $this->insertOption($carrierId, 'return_' . 'MYPARCELBE_INSURANCE_MAX_AMOUNT_EU', '0');
        }
    }

    /**
     * @param  int    $carrierId
     * @param  string $optionName
     * @param  string $optionValue
     *
     * @return void
     */
    private function addOption(int $carrierId, string $optionName, string $optionValue): void
    {
        $table = $this->carrierConfigurationTable;

        $query = <<<SQL
INSERT INTO `$table` (id_carrier, name, value) VALUES (`$carrierId`, `$optionName`, `$optionValue`);
SQL;

        $this->db->execute($query);
    }

    /**
     * Inserts option with $optionValue for carrier if it does not exist yet. Leaves the current option value otherwise.
     *
     * @param  int    $carrierId
     * @param  string $optionName
     * @param  string $optionValue
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function insertOption(int $carrierId, string $optionName, string $optionValue): void
    {
        if ($this->optionExists($carrierId, $optionName)) {
            return;
        }

        $this->addOption($carrierId, $optionName, $optionValue);
    }

    /**
     * @param  int    $carrierId
     * @param  string $optionName
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    private function optionExists(int $carrierId, string $optionName): bool
    {
        $table = $this->carrierConfigurationTable;

        $query = <<<SQL
SELECT DISTINCT id_carrier, name, value FROM `$table` WHERE id_carrier = `$carrierId` AND name = `$optionName`
SQL;

        return (new Collection($this->db->executeS($query)))->isNotEmpty();
    }
}
