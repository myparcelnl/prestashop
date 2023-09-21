<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use DbQuery;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\Sdk\src\Concerns\HasInstance;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

abstract class MockPsDb extends BaseMock implements StaticMockInterface
{
    public const ENTITY_LIST = [
        MyparcelnlCarrierMapping::class,
        MyparcelnlCartDeliveryOptions::class,
        MyparcelnlOrderData::class,
        MyparcelnlOrderShipment::class,
        MyparcelnlProductSettings::class,
    ];
    use HasInstance;

    /**
     * @var array<string, array>
     */
    private static $database = [];

    /**
     * @param  string $table
     * @param  array  $row
     *
     * @return void
     */
    public static function insertRow(string $table, array $row): void
    {
        if (! isset(self::$database[$table])) {
            self::$database[$table] = [];
        }

        self::$database[$table][] = $row;
    }

    /**
     * @param  string      $table
     * @param  array       $rows
     * @param  null|string $incrementingIdColumn
     *
     * @return void
     */
    public static function insertRows(
        string  $table,
        array   $rows,
        ?string $incrementingIdColumn = null
    ): void {
        $i = 0;

        foreach ($rows as $row) {
            if ($incrementingIdColumn) {
                $row[$incrementingIdColumn] = $i++;
            }

            MockPsDb::insertRow($table, $row);
        }
    }

    /**
     * @return void
     */
    public static function reset(): void
    {
        self::$database = [];
    }

    /**
     * @param  array $data
     *
     * @return void
     */
    public static function setData(array $data): void
    {
        self::$database = $data;
    }

    /**
     * @param  string|\DbQuery $query
     *
     * @return bool
     */
    public function execute($query): bool
    {
        try {
            $this->executeS($query);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param  string|\DbQuery $query
     *
     * @return array
     * @throws \PrestaShopException
     */
    public function executeS($query): array
    {
        $query = $this->resolveQuery($query);

        return $query ?? [];
    }

    /**
     * @param $query
     *
     * @return void
     * @throws \PrestaShopException
     */
    public function resolveQuery($query): ?array
    {
        $queryString = $this->createQueryString($query);

        if (Str::startsWith(strtolower($queryString), 'select')) {
            return $this->resolveSelect($queryString);
        }

        if (Str::startsWith(strtolower($queryString), 'create table')) {
            $this->resolveCreateTable($queryString);
        } elseif (Str::startsWith(strtolower($queryString), 'insert')) {
            $this->resolveInsert($queryString);
        }

        return null;
    }

    /**
     * @param $query
     *
     * @return string
     * @throws \PrestaShopException
     */
    private function createQueryString($query): string
    {
        return preg_replace('/\s+/', ' ', $query instanceof DbQuery ? $query->build() : $query);
    }

    /**
     * @param $table
     *
     * @return string
     */
    private function getEntityOrObjectModel($table): string
    {
        $class    = Str::studly($table);
        $entities = self::ENTITY_LIST;

        foreach ($entities as $entity) {
            if (! Str::endsWith($entity, $class)) {
                continue;
            }

            return $entity;
        }

        return $class;
    }

    /**
     * @param  string $queryString
     *
     * @return void
     */
    private function resolveCreateTable(string $queryString): void
    {
        $table = preg_replace('/^CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`? \(.+$/i', '$1', $queryString);

        self::$database[$table] = [];
    }

    private function resolveInsert(string $queryString): void
    {
        preg_match('/INSERT INTO (?:IF NOT EXISTS )?`?(\w+)`? \((.+)\) VALUES \((.+)\)$/i', $queryString, $matches);

        $table   = $matches[1];
        $columns = $matches[2];
        $values  = $matches[3];

        $columns = array_map('trim', explode(',', $columns));
        $values  = array_map('trim', explode(',', $values));

        $entity = $this->getEntityOrObjectModel($table);

        $data = array_combine($columns, $values);

        MockPsDb::insertRow($table, $data);
    }

    /**
     * @param  string $queryString
     *
     * @return array
     */
    private function resolveSelect(string $queryString): array
    {
        // parse the parts of the select query, find the table name and the where clause
        preg_match('/SELECT\s+(.*?)\s+FROM\s+(.*?)(\s+WHERE\s+(.*?)\s*)?$/i', $queryString, $matches);

        $matches = array_map('trim', $matches);

        $columns = $matches[1];
        $table   = $matches[2];
        $where   = $matches[3] ?? null;

        $tableData = self::$database[$table] ?? [];

        if (empty($where)) {
            $data = $tableData;
            // get only requested columns

        } else {
            $data = Arr::where($tableData, function (array $item) use ($where) {
                return true;
            });
        }

        if (empty($columns) || '*' === $columns) {
            return $data;
        }

        return array_map(static function (array $item) use ($columns) {
            return Arr::only($item, $columns);
        }, $data);
    }
}
