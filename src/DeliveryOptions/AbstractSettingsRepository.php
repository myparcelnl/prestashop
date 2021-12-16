<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Exception;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use MyParcelNL\Sdk\src\Support\Collection;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractSettingsRepository
{
    use HasInstance;

    /**
     * @var \MyParcelNL\Sdk\src\Support\Collection
     */
    protected static $data = [];

    /**
     * @var string
     */
    protected static $table;

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function get(): Collection
    {
        if (empty(self::$data)) {
            $result = Db::getInstance()
                ->executeS($this->getQuery());

            self::$data = new Collection($result);
        }

        return self::$data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getQuery(): string
    {
        $table = Table::withPrefix($this->getTable());

        return <<<SQL
SELECT *
FROM $table
SQL;
    }

    /**
     * @throws \Exception
     */
    protected function getTable(): string
    {
        if (! static::$table) {
            throw new Exception('Static property $table must be set');
        }

        return static::$table;
    }
}
