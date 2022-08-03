<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Order\Repository;

use Db;
use DbQuery;
use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\DefaultLogger;

class CarrierConfigurationRepository extends Repository
{
    //    public function __construct(DatabaseStorage $storage, ApiServiceInterface $api)
    //    {
    //        parent::__construct($storage, $api);
    //    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function get(): Collection
    {
        return $this->getTableCached(Table::TABLE_CARRIER_CONFIGURATION);
    }

    /**
     * @param  int $carrierId
     *
     * @return array
     */
    public function getByCarrier(int $carrierId): array
    {
        return $this->get()
            ->first(function (array $item) use ($carrierId) {
                return (int) $item['id_carrier'] === $carrierId;
            });
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getDeliverySettings(): Collection
    {
        return $this->getTableCached(Table::TABLE_DELIVERY_SETTINGS);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getOrderData(): Collection
    {
        return $this->getTableCached(Table::TABLE_ORDER_DATA);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getOrderLabels(): Collection
    {
        return $this->getTableCached(Table::TABLE_ORDER_LABEL);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getProductConfigurations(): Collection
    {
        return $this->getTableCached(Table::TABLE_PRODUCT_CONFIGURATION);
    }

    /**
     * @param  string $table
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function getTableCached(string $table): Collection
    {
        return $this->retrieve($table, function () use ($table) {
            $query = new DbQuery();
            $query->select('*');
            $query->from($table);

            try {
                $result = Db::getInstance()
                    ->executeS($query);
            } catch (\Throwable $exception) {
                DefaultLogger::error($exception->getMessage(), compact('exception'));
                return [];
            }

            return new Collection($result);
        });
    }
}
