<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

/**
 * @see \Configuration
 */
abstract class MockPsConfiguration extends BaseMock implements StaticMockInterface
{
    private static $configuration = [];

    public static function get(string $key, $default = null)
    {
        return self::$configuration[$key] ?? $default;
    }

    public static function reset(): void
    {
        self::$configuration = [];
    }

    public static function set(string $key, $value): void
    {
        self::$configuration[$key] = $value;
    }

    public static function setMany(array $configuration): void
    {
        self::$configuration = $configuration;
    }
}
