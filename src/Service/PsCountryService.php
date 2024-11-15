<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Country;
use Country as PsCountry;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\PrestaShop\Contract\PsCountryServiceInterface;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @template T of PsCountry
 * @extends \MyParcelNL\PrestaShop\Service\PsSpecificObjectModelService<T>
 */
final class PsCountryService extends PsSpecificObjectModelService implements PsCountryServiceInterface
{
    /**
     * @var array<string, int|false>
     */
    private static array $countryIdIsoCache = [];

    /**
     * @TODO: Remove this when Carrier Capabilities service is implemented.
     *
     * @param  string $carrierName
     *
     * @return array
     */
    public function getCountriesForCarrier(string $carrierName): array
    {
        // Resolve carrier identifier
        [$resolvedCarrierName] = explode(':', $carrierName);

        $platform            = Platform::getPlatform();
        $allCarrierCountries = Pdk::get('countriesPerPlatformAndCarrier')[$platform] ?? [];
        $countriesForCarrier = $allCarrierCountries[$resolvedCarrierName] ?? [];

        if (true === ($countriesForCarrier['fakeDelivery'] ?? null)) {
            $allCountries = CountryCodes::ALL;
        } else {
            $allCountries = array_merge(
                $countriesForCarrier['deliveryCountries'] ?? [],
                $countriesForCarrier['pickupCountries'] ?? []
            );

            // remove duplicates and sort
            $allCountries = array_unique($allCountries);
            sort($allCountries);
        }

        return $allCountries;
    }

    /**
     * @param  string $isoCode
     *
     * @return ?int
     */
    public function getCountryIdByIsoCode(string $isoCode): ?int
    {
        // Also cache "false" values to prevent multiple queries for non-existing countries
        if (null === (self::$countryIdIsoCache[$isoCode] ?? null)) {
            self::$countryIdIsoCache[$isoCode] = Country::getByIso(Str::upper($isoCode));
        }

        return self::$countryIdIsoCache[$isoCode] ?: null;
    }

    /**
     * @param  array $isoCodes
     *
     * @return int[]
     */
    public function getCountryIdsByIsoCodes(array $isoCodes): array
    {
        return array_reduce($isoCodes, function (array $carry, string $countryIso) {
            $countryId = $this->getCountryIdByIsoCode($countryIso);

            if ($countryId) {
                $carry[$countryIso] = $countryId;
            }

            return $carry;
        }, []);
    }

    /**
     * @return string
     */
    protected function getClass(): string
    {
        return PsCountry::class;
    }
}
