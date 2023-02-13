<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Sdk\src\Support\Collection;

class Upgrade1_8_2 extends AbstractUpgrade
{
    private $carrierConfigurationTable;

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function upgrade(): void
    {
        if ($this->module->isBE()) {
            return;
        }

        $this->addInsuranceOptionsBeForCarriers();
    }

    /**
     * Adds insurance_from_price and insurance_max_amount to carrier configuration if not already present
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceOptionsBeForCarriers(): void
    {
        $this->carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $configName                      = Constant::INSURANCE_CONFIGURATION_NAME;

        $query   = <<<SQL
SELECT DISTINCT id_carrier, name, value FROM $this->carrierConfigurationTable WHERE name LIKE '%$configName'
SQL;
        $records = new Collection($this->db->executeS($query));

        foreach ($records as $record) {
            $carrierId = (int) $record['id_carrier'];

            $this->insertOption($carrierId, Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT_BE, '0');
            $this->insertOption($carrierId, 'return_' . Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT_BE, '0');
        }
    }

    /**
     * @param  int    $carrierId
     * @param  string $optionName
     * @param  string $optionValue
     *
     * @return bool
     */
    private function addOption(int $carrierId, string $optionName, string $optionValue): bool
    {
        $query = <<<SQL
INSERT INTO $this->carrierConfigurationTable (id_carrier, name, value) VALUES ($carrierId, '$optionName', '$optionValue');
SQL;
        return $this->db->execute($query);
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
        if (! $this->optionExists($carrierId, $optionName)) {
            $this->addOption($carrierId, $optionName, $optionValue);
        }
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
        $query = <<<SQL
SELECT DISTINCT id_carrier, name, value FROM $this->carrierConfigurationTable WHERE id_carrier = $carrierId AND name = '$optionName'
SQL;
        return (new Collection($this->db->executeS($query)))->isNotEmpty();
    }
}
