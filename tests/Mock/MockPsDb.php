<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use DbQuery;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\Sdk\src\Concerns\HasInstance;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

abstract class MockPsDb extends BaseMock implements StaticMockInterface
{
    use HasInstance;

    /**
     * @var array<string, array>[]
     */
    private static $database = [];

    /**
     * @param  string $table
     * @param  array  $where
     *
     * @return void
     */
    public static function deleteRows(string $table, array $where): void
    {
        $tableData = self::$database[$table] ?? [];

        self::$database[$table] = self::resolveWhere($tableData, $where);
    }

    /**
     * @return array<string, array>[]
     */
    public static function getDatabase(): array
    {
        return self::$database;
    }

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

            self::insertRow($table, $row);
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
     * @param  string $table
     * @param  array  $data
     *
     * @return void
     */
    public static function updateRow(string $table, array $data): void
    {
        self::deleteRows($table, $data);
        self::insertRow($table, $data);
    }

    /**
     * @param  array $tableData
     * @param  array $where
     *
     * @return array
     */
    private static function resolveWhere(array $tableData, array $where): array
    {
        return Arr::where($tableData, static function (array $item) use ($where) {
            foreach ($where as $key => $value) {
                if ($item[$key] === $value) {
                    continue;
                }

                return true;
            }

            return false;
        });
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
     * @param  DbQuery|string $query
     *
     * @return mixed
     * @throws \PrestaShopException
     */
    public function getValue($query)
    {
        $result = $this->resolveQuery($query);

        return Arr::first(array_values(Arr::first($result)));
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
     * @param  null|string $where
     *
     * @return array|null
     */
    protected function resolveWhereString(?string $where): ?array
    {
        if (! $where) {
            return null;
        }

        preg_match_all('/(\w+)\s*=\s*(\w+)/', $where, $matches);

        return array_combine($matches[1], $matches[2]);
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

        [, $table, $columns, $values] = $matches;

        $columns = array_map('trim', explode(',', $columns));
        $values  = array_map('trim', explode(',', $values));

        $data = array_combine($columns, $values);

        self::insertRow($table, $data);
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

        [, $columns, $table, $where] = $matches;

        $wheres = $this->resolveWhereString($where);

        $tableData = self::$database[$table] ?? [];

        if (empty($wheres)) {
            $data = $tableData;
        } else {
            $data = self::resolveWhere($tableData, $wheres);
        }

        if (empty($columns) || '*' === $columns) {
            return $data;
        }

        return array_map(static function (array $item) use ($columns) {
            return Arr::only($item, $columns);
        }, $data);
    }
}
