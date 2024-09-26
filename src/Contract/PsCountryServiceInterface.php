<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use Country as PsCountry;

/**
 * @extends \MyParcelNL\PrestaShop\Contract\PsSpecificObjectModelServiceInterface<PsCountry>
 */
interface PsCountryServiceInterface extends PsSpecificObjectModelServiceInterface
{
    /**
     * @TODO: Replace this when Carrier Capabilities service is implemented.
     *
     * @param  string $carrierName
     *
     * @return array
     */
    public function getCountriesForCarrier(string $carrierName): array;

    /**
     * @param  string $isoCode
     *
     * @return ?int
     */
    public function getCountryIdByIsoCode(string $isoCode): ?int;

    /**
     * @param  array $isoCodes
     *
     * @return int[]
     */
    public function getCountryIdsByIsoCodes(array $isoCodes): array;
}
