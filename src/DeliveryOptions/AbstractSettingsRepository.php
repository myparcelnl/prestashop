<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Entity\Cache;
use MyParcelNL\Sdk\src\Support\Collection;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

/**
 * @deprecated
 */
abstract class AbstractSettingsRepository
{
    /**
     * @throws \Exception
     */
    abstract protected function getTable(): string;

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function get(): Collection
    {
        $className = str_replace('\\', '_', static::class);

        return Cache::remember($className, function () {
            $result = Db::getInstance()
                ->executeS($this->getQuery());
            return new Collection($result);
        });
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
}
