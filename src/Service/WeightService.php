<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Configuration;

class WeightService extends \MyParcelNL\Pdk\Base\Service\WeightService
{
    /**
     * @param  int|float $weight
     * @param  string    $unit leave empty, prestashop configured weight unit will be used
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit = ''): int
    {
        return parent::convertToGrams($weight, strtolower(Configuration::get('PS_WEIGHT_UNIT')));
    }
}
