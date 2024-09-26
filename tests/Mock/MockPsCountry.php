<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;

/**
 * @see \CountryCore
 * @extends \MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModel<\Country>
 */
abstract class MockPsCountry extends ObjectModel
{
    /**
     * Get a country ID with its iso code.
     *
     * @param  string $isoCode Country iso code
     * @param  bool   $active  return only active countries
     *
     * @return int|bool
     * @see \CountryCore::getByIso()
     */
    public static function getByIso(string $isoCode, bool $active = false)
    {
        $found = self::firstWhere(['iso_code' => $isoCode]);

        if (! $found) {
            return false;
        }

        return $found->id;
    }

    /**
     * @param  int $id_zone
     * @param  int $id_lang
     *
     * @return array[]
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @see \CountryCore::getCountriesByZoneId()
     */
    public static function getCountriesByZoneId(int $id_zone, int $id_lang): array
    {
        $ids = MockPsCountries::getCountriesByZoneId($id_zone);

        return array_map(static function (int $id) {
            return (new static($id))->toArray();
        }, $ids);
    }

    protected static function getTable(): string
    {
        return 'country';
    }

    /**
     * @param  array $countryIds
     * @param  int   $zoneId
     *
     * @return void
     * @see \CountryCore::affectZoneToSelection()
     */
    public function affectZoneToSelection(array $countryIds, int $zoneId): void
    {
        MockPsCountries::setZoneCountries($zoneId, $countryIds);
    }
}
