<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Configuration;
use MyParcelNL\Pdk\Base\Service\WeightService as PdkWeightService;

class WeightService extends PdkWeightService
{
    /**
     * @param int|float $weight
     * @param string    $unit leave empty, prestashop configured weight unit will be used
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit = ''): int
    {
        return parent::convertToGrams($weight, strtolower(Configuration::get('PS_WEIGHT_UNIT')));
    }
}
