<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use Configuration;
use MyParcelNL\Pdk\Base\Service\WeightService;

class PsWeightService extends WeightService
{
    /**
     * @param  int|float $weight
     * @param  string    $unit leave empty, prestashop configured weight unit will be used
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit = ''): int
    {
        return parent::convertToGrams($weight, $this->normalizeUnit(strtolower(Configuration::get('PS_WEIGHT_UNIT'))));
    }

    /**
     * Since weight unit is an unrestricted string input in Prestashop, we need to normalize it to a unit we can use.
     *
     * @param  string $unit
     *
     * @return string
     */
    private function normalizeUnit(string $unit): string
    {
        switch ($unit) {
            case 'kg':
            case 'kgs':
            case 'kilogram':
            case 'kilograms':
                return self::UNIT_KILOGRAMS;
            case 'lb':
            case 'lbs':
            case 'pound':
            case 'pounds':
                return self::UNIT_POUNDS;
            case 'oz':
            case 'ounce':
            case 'ounces':
                return self::UNIT_OUNCES;
            default:
                return self::UNIT_GRAMS;
        }
    }
}
