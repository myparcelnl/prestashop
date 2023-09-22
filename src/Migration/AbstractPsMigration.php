<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use Db;
use DbQuery;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Base\Support\Collection;

abstract class AbstractPsMigration implements MigrationInterface
{
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

        $this->db->execute($query);
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

        return $this->db->getValue($query) ?: null;
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
    protected function insertRows(string $table, array $records, bool $useReplace = true): void
    {
        $this->db->insert($table, $records, false, false, $useReplace ? Db::REPLACE : Db::INSERT);
    }
}
