<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

final class MockPsCountries extends BaseMock implements StaticMockInterface
{
    /**
     * @var array<int, int[]>
     */
    private static array $zones = [];

    /**
     * @param  int $zoneId
     *
     * @return int[]
     */
    public static function getCountriesByZoneId(int $zoneId): array
    {
        return self::$zones[$zoneId] ?? [];
    }

    public static function reset(): void
    {
        self::$zones = [];
    }

    /**
     * @param  int   $zoneId
     * @param  int[] $countryIds
     *
     * @return void
     */
    public static function setZoneCountries(int $zoneId, array $countryIds): void
    {
        self::$zones[$zoneId] = $countryIds;
    }
}
