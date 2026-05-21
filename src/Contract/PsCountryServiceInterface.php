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
