<?php

use Gett\MyparcelBE\Service\CarrierConfigurationProvider;

class Cart extends CartCore
{
    protected function getPackageShippingCostFromModule(Carrier $carrier, $shipping_cost, $products)
    {
        $configurationPsCarriers = CarrierConfigurationProvider::get($carrier->id, 'carrierType');

        if (!is_null($configurationPsCarriers)) {
            $carrier->external_module_name = 'myparcelbe';
            $carrier->is_module = true;
            $carrier->active = 1;
            $carrier->range_behavior = 1;
            $carrier->need_range = 1;
            $carrier->shipping_external = true;
            $carrier->range_behavior = 0;
            $carrier->shipping_method = 2;
        }

        if (!$carrier->shipping_external) {
            return $shipping_cost;
        }

        /** @var CarrierModule $module */
        $module = Module::getInstanceByName($carrier->external_module_name);

        if (!Validate::isLoadedObject($module)) {
            return false;
        }

        if (property_exists($module, 'id_carrier')) {
            $module->id_carrier = $carrier->id;
        }

        if (!$carrier->need_range) {
            return $module->getOrderShippingCostExternal($this);
        }

        if (method_exists($module, 'getPackageShippingCost')) {
            $shipping_cost = $module->getPackageShippingCost($this, $shipping_cost, $products);
        } else {
            $shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
        }

        return $shipping_cost;
    }
}
