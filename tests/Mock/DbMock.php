<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

abstract class DbMock extends BaseMock implements StaticMockInterface
{
    /**
     * @param  string $table
     *
     * @return array
     */
    public static function all(string $table): array
    {
        return MockPsDb::getDatabase()[$table] ?? [];
    }

    /**
     * @param  null|array $where
     *
     * @return void
     */
    public static function deleteRows(?array $where = null): void
    {
        MockPsDb::deleteRows(static::getTableName(), $where);
    }

    /**
     * @template-covariant T
     * @param  array $where
     * @param  T     $default
     *
     * @return T|array
     */
    public static function firstWhere(array $where, $default = null): ?array
    {
        return MockPsDb::firstWhere(static::getTableName(), $where) ?? $default;
    }

    /**
     * @return void
     */
    public static function reset(): void
    {
        MockPsDb::deleteRows(static::getTableName());
    }

    /**
     * @param  array $row
     *
     * @return void
     */
    public static function updateRow(array $row): void
    {
        MockPsDb::updateRow(static::getTableName(), $row);
    }

    /**
     * @param  array $rows
     *
     * @return void
     */
    public static function updateRows(array $rows): void
    {
        foreach ($rows as $row) {
            self::updateRow($row);
        }
    }

    abstract protected static function getTableName(): string;
}
