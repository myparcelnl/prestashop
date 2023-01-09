<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use Carrier;
use Configuration;
use Product;

class ShippingOptions
{
    /**
     * @var \Context
     */
    private $context;

    public function __construct()
    {
        $this->context = \Context::getContext();
    }

    public function get(int $carrierId, \Address $address)
    {
        $carrier = new Carrier($carrierId);
        $taxRate = ($carrier->getTaxesRate($address) / 100) + 1;

        $includeTax      = ! Product::getTaxCalculationMethod((int) $this->context->cart->id_customer)
            && (int) Configuration::get('PS_TAX');
        $displayTaxLabel = (Configuration::get('PS_TAX') && ! Configuration::get('AEUC_LABEL_TAX_INC_EXC'));

        return [
            'tax_rate'          => ($includeTax) ? $taxRate : 1,
            'include_tax'       => $includeTax,
            'display_tax_label' => $displayTaxLabel,
        ];
    }
}
