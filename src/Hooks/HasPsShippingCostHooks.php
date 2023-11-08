<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

trait HasPsShippingCostHooks
{
    /**
     * @param $cart
     * @param $shippingCost
     *
     * @return mixed
     */
    public function getOrderShippingCost($cart, $shippingCost)
    {
        return $shippingCost;
    }

    /**
     * @param  \Cart $params
     *
     * @return bool
     */
    public function getOrderShippingCostExternal($params): bool
    {
        return true;
    }
}
