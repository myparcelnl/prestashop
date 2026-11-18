<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use Configuration;
use MyParcelNL\Pdk\Base\Service\WeightService;

class PsWeightService extends WeightService
{
    /**
     * Convert a weight to grams. Pass the unit when the weight has one of its own, such as a weight
     * limit from the capabilities API. Leave the unit empty for a weight in the PrestaShop weight unit
     * (PS_WEIGHT_UNIT), such as a product weight.
     *
     * @param  int|float $weight
     * @param  string    $unit one of the WeightServiceInterface::UNIT_* constants, or empty for PS_WEIGHT_UNIT
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit = ''): int
    {
        if ('' === $unit) {
            $unit = $this->normalizeUnit(strtolower((string) Configuration::get('PS_WEIGHT_UNIT')));
        }

        return parent::convertToGrams($weight, $unit);
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
