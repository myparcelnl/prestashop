<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use Db;
use DbQuery;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use Throwable;

abstract class AbstractPsMigration implements MigrationInterface
{
    public const LEGACY_TABLE_ORDER_LABEL           = 'myparcelnl_order_label';
    public const LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    public const LEGACY_TABLE_DELIVERY_SETTINGS     = 'myparcelnl_delivery_settings';
    public const LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';

    /**
     * @var \Db
     */
    protected $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * @param  string $table
     * @param  string $column
     * @param  array  $values
     *
     * @return void
     */
    protected function deleteWhere(string $table, string $column, array $values): void
    {
        $valuesString = implode("', '", $values);
        $query        = "DELETE FROM `$table` WHERE `$column` IN ('$valuesString')";

        $this->execute($query);
    }

    /**
     * @param  string|\DbQuery $query
     *
     * @return mixed
     */
    protected function execute($query)
    {
        return $this->withErrorHandling(function () use ($query) {
            $this->db->execute($query);
        });
    }

    /**
     * @param  string        $from
     * @param  callable|null $callback
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    protected function getAllRows(string $from, callable $callback = null): Collection
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

    /**
     * @param  string $table
     * @param  string $column
     * @param  string $where
     *
     * @return mixed
     */
    protected function getDbValue(string $table, string $column, string $where)
    {
        $query = new DbQuery();
        $query->select($column);
        $query->from($table);
        $query->where($where);

        return $this->withErrorHandling(function () use ($query) {
            return $this->db->getValue($query);
        });
    }

    /**
     * @param  string|DbQuery $query
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection – Collection of associative arrays
     */
    protected function getRows($query): Collection
    {
        $rows = $this->withErrorHandling(function () use ($query) {
            $result = $this->db->executeS($query);

            return is_array($result) ? $result : [];
        });

        return new Collection($rows ?? []);
    }

    /**
     * @param  string $table
     * @param  array  $records
     * @param  bool   $useReplace
     *
     * @return void
     */
    protected function insertRows(string $table, array $records, bool $useReplace = true): void
    {
        $this->withErrorHandling(function () use ($useReplace, $records, $table) {
            $this->db->insert($table, $records, false, false, $useReplace ? Db::REPLACE : Db::INSERT);
        });
    }

    /**
     * @template T
     * @param  callable<T> $callback
     *
     * @return T
     */
    protected function withErrorHandling(callable $callback)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            Logger::error(
                '[Migration] Failed to execute query',
                [
                    'message'   => $e->getMessage(),
                    'migration' => static::class,
                ]
            );
        }

        return null;
    }
}
