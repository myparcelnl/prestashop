<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

/**
 * @see \Configuration
 * @see \PrestaShop\PrestaShop\Adapter\Configuration
 */
class MockPsConfiguration extends DbMock
{
    public static function get(string $key, $default = null)
    {
        $first = self::firstWhere(['name' => $key]);

        return $first['value'] ?? $default;
    }

    public static function remove(string $key): void
    {
        self::deleteRows(['name' => $key]);
    }

    public static function set(string $key, $value): void
    {
        self::updateRow(['name' => $key, 'value' => $value]);
    }

    public static function setMany(array $configurations): void
    {
        foreach ($configurations as $key => $value) {
            self::set($key, $value);
        }
    }

    protected static function getTableName(): string
    {
        return 'configuration';
    }
}
