<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
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

            $optionName = Constant::INSURANCE_CONFIGURATION_FROM_PRICE;
            if (!$this->optionExists($carrierId, $optionName)) {
                $this->addOption($carrierId, $optionName, '0');
            }

            $optionName = Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT;
            if (!$this->optionExists($carrierId, $optionName)) {
                $this->addOption($carrierId, $optionName, (string) $maxAmount);
            }
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
