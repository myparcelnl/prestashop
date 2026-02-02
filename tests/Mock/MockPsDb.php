<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use DbQuery;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\Sdk\Concerns\HasInstance;
use MyParcelNL\Sdk\Support\Str;
use PDOStatement;
use PrestaShopException;
use Throwable;

abstract class MockPsDb extends BaseMock implements StaticMockInterface
{
    use HasInstance;

    /**
     * @var array<string, array>[]
     */
    private static $database = [];

    /**
     * @param  string     $table
     * @param  null|array $where
     *
     * @return void
     */
    public static function deleteRows(string $table, ?array $where = null): void
    {
        $tableData = self::$database[$table] ?? [];

        if (null === $where) {
            self::$database[$table] = [];

            return;
        }

        self::$database[$table] = self::resolveWhere($tableData, $where, true);
    }

    /**
     * @param  string $table
     * @param  array  $where
     *
     * @return null|array
     */
    public static function firstWhere(string $table, array $where): ?array
    {
        $result = self::where($table, $where);

        return Arr::first($result);
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
     * @param  string $table
     * @param  array  $where
     *
     * @return array
     */
    public static function where(string $table, array $where): array
    {
        $tableData = self::$database[$table] ?? [];

        return self::resolveWhere($tableData, $where);
    }

    /**
     * @param  array $tableData
     * @param  array $where
     * @param  bool  $invert
     *
     * @return array
     */
    private static function resolveWhere(array $tableData, array $where, bool $invert = false): array
    {
        $find = static function (array $item, array $where) {
            foreach ($where as $key => $value) {
                if (is_array($value) && ! in_array($item[$key], $value, true)) {
                    continue;
                }

                if ($item[$key] !== $value) {
                    continue;
                }

                return true;
            }

            return false;
        };

        return Arr::where($tableData, static function (array $item) use ($find, $where, $invert) {
            return $find($item, $where) !== $invert;
        });
    }

    /**
     * Executes a query.
     *
     * @param  string|DbQuery $sql
     * @param  bool           $useCache
     *
     * @return bool
     * @noinspection BadExceptionsProcessingInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function execute($sql, $useCache = true): bool
    {
        try {
            $this->executeS($sql);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Executes return the result of $sql as array.
     *
     * @param  string|DbQuery $sql   Query to execute
     * @param  bool           $array Return an array instead of a result object (deprecated since 1.5.0.1, use query method instead)
     * @param  bool           $useCache
     *
     * @return array|bool|PDOStatement|resource|null – preserving the original return type
     * @throws \PrestaShopException
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function executeS($sql, $array = true, $useCache = true)
    {
        return $this->resolveQuery($sql);
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
     * @param  string|\DbQuery $query
     *
     * @return array|null|false
     * @throws \PrestaShopException
     */
    protected function resolveQuery($query)
    {
        // These two cases can be used to test unhappy flows, as the real DbQuery can throw errors and return something other than array|null
        if (! $query) {
            throw new PrestaShopException('Invalid query');
        }

        if ('false' === $query) {
            return false;
        }

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

        $wheres = [];

        $parseValue = static function (string $string) {
            return is_numeric($string) ? (int) $string : trim($string, '\'"');
        };

        // resolve where name = value
        preg_match_all('/(\w+)\s*=\s*(\w+)/', $where, $matches);

        foreach ($matches[1] as $key => $column) {
            $wheres[$column] = $parseValue($matches[2][$key]);
        }

        // resolve where name in (value1, value2, ...)
        preg_match_all('/(\w+)\s+in\s+\((.+)\)/', $where, $whereInMatches);

        foreach ($whereInMatches[1] as $key => $column) {
            $wheres[$column] = array_map($parseValue, explode(',', $whereInMatches[2][$key]));
        }

        return $wheres;
    }

    /**
     * @param  string|\DbQuery $query
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
