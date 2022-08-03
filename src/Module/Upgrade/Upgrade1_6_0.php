<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Upgrade;

use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\Sdk\src\Support\Collection;

class Upgrade1_6_0 extends AbstractUpgrade
{
    private $carrierConfigurationTable;

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function upgrade(): void
    {
        $this->addInsuranceOptionsForCarriers();
        $this->addInsuranceBelgiumForCarriers();
    }

    /**
     * Adds insurance_from_price and insurance_max_amount to carrier configuration if not already present
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceOptionsForCarriers(): void
    {
        $this->carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $configName                      = Constant::INSURANCE_CONFIGURATION_NAME;

        $query   = <<<SQL
SELECT DISTINCT id_carrier, name, value FROM $this->carrierConfigurationTable WHERE name LIKE '%$configName'
SQL;
        $records = new Collection($this->db->executeS($query));

        foreach ($records as $record) {
            $carrierId = (int) $record['id_carrier'];
            $maxAmount = '1' === $record['value']
                ? 500
                : 0;

            $this->insertOption($carrierId, Constant::INSURANCE_CONFIGURATION_FROM_PRICE, '0');
            $this->insertOption($carrierId, 'return_' . Constant::INSURANCE_CONFIGURATION_FROM_PRICE, '0');

            $this->insertOption($carrierId, Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT, (string) $maxAmount);
            $this->insertOption($carrierId, 'return_' . Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT, (string) $maxAmount);
        }
    }

    /**
     * For all carriers in PrestaShop adds the 'insurance Belgium' setting and sets it to true.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function addInsuranceBelgiumForCarriers(): void
    {
        $this->carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $configName                      = Constant::INSURANCE_CONFIGURATION_BELGIUM;

        $query   = <<<SQL
SELECT DISTINCT id_carrier FROM $this->carrierConfigurationTable
SQL;
        $records = new Collection($this->db->executeS($query));

        foreach ($records as $record) {
            $carrierId = (int) $record['id_carrier'];

            $this->insertOption($carrierId, $configName, '1');
            $this->insertOption($carrierId, 'return_' . $configName, '1');
        }
    }

    /**
     * Inserts option with $optionValue for carrier if it does not exist yet. Leaves the current option value otherwise.
     *
     * @param int    $carrierId
     * @param string $optionName
     * @param string $optionValue
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
     * @param int    $carrierId
     * @param string $optionName
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    private function optionExists(int $carrierId, string $optionName): bool
    {
        $query   = <<<SQL
SELECT DISTINCT id_carrier, name, value FROM $this->carrierConfigurationTable WHERE id_carrier = $carrierId AND name = '$optionName'
SQL;
        return (new Collection($this->db->executeS($query)))->isNotEmpty();
    }

    /**
     * @param int    $carrierId
     * @param string $optionName
     * @param string $optionValue
     *
     * @return bool
     */
    private function addOption(int $carrierId, string $optionName, string $optionValue): bool
    {
        $query      = <<<SQL
INSERT INTO $this->carrierConfigurationTable (id_carrier, name, value) VALUES ($carrierId, '$optionName', '$optionValue');
SQL;
        return $this->db->execute($query);
    }
}
