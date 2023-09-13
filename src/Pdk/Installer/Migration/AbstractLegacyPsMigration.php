<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Migration;

use Db;
use DbQuery;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Database\Table;

abstract class AbstractLegacyPsMigration extends AbstractPsMigration
{
    public const LEGACY_TABLE_ORDER_LABEL           = 'myparcelnl_order_label';
    public const LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    public const LEGACY_TABLE_DELIVERY_SETTINGS     = 'myparcelnl_delivery_settings';
    public const LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';

    public function down(): void
    {
        // do nothing
    }

    protected function deleteWhere(string $table, string $column, array $values): void
    {
        $valuesString = implode("', '", $values);
        $query        = "DELETE FROM `$table` WHERE `$column` IN ('$valuesString')";

        $this->db->execute($query);
    }

    /**
     * @param  string        $from
     * @param  callable|null $callback
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    protected function getAll(string $from, callable $callback = null): Collection
    {
        $query = new DbQuery();
        $query
            ->select('*')
            ->from($from);

        if ($callback) {
            $callback($query);
        }

        return $this->getRows($query);
    }

    final protected function getCarrierConfigurationTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_CARRIER_CONFIGURATION);
    }

    final protected function getDeliverySettingsTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_DELIVERY_SETTINGS);
    }

    final protected function getOrderLabelTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_ORDER_LABEL);
    }

    final protected function getProductConfigurationTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_PRODUCT_CONFIGURATION);
    }

    /**
     * @param  string|DbQuery $query
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    protected function getRows($query): Collection
    {
        return new Collection($this->db->executeS($query));
    }

    /**
     * @param  string $table
     * @param  array  $records
     * @param  bool   $useReplace
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    protected function insert(string $table, array $records, bool $useReplace = true): void
    {
        $this->db->insert($table, $records, false, false, $useReplace ? Db::REPLACE : Db::INSERT);
    }

    protected function insertRecords(): void {}
}
