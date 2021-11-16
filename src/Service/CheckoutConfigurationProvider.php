<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use PrestaShop\PrestaShop\Adapter\Configuration;

class CheckoutConfigurationProvider extends AbstractConfigurationProvider
{
    /**
     * @param  string $string
     *
     * @return void
     */
    public static function get(string $string)
    {
        Configuration::get($string);


    }
}
