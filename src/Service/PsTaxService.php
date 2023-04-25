<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Plugin\Service\AbstractTaxService;

class PsTaxService extends AbstractTaxService
{
    /**
     * @param  float $basePrice
     *
     * @return float
     */
    public function getShippingDisplayPrice(float $basePrice): float
    {
        return $basePrice;
    }
}
