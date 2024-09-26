<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPsTools extends BaseMock
{
    /**
     * @var array
     */
    private static $values = [];

    /**
     * @return array
     */
    public static function getAllValues(): array
    {
        return self::$values;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public static function getValue(string $key)
    {
        return self::$values[$key] ?? null;
    }

    public static function reset(): void
    {
        self::$values = [];
    }

    /**
     * @param  array $values
     *
     * @return void
     * @internal
     */
    public static function setValues(array $values): void
    {
        self::$values = $values;
    }
}
