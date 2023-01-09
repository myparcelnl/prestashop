<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Service;

use Cart;
use Context;
use MyParcelNL;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\DeliveryOptions\DeliveryOptionsManager;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\PrestaShop\Service\CarrierConfigurationProvider;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ModuleService
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \MyParcelNL
     */
    private $module;

    /**
     * @param  \MyParcelNL $module
     */
    public function __construct(MyParcelNL $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();
    }

    /**
     * @return array
     */
    public function getHooks(): array
    {
        $reflectionClass = new ReflectionClass(MyParcelNL::class);

        $hooks = (new Collection($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC)))
            ->filter(function (ReflectionMethod $method) {
                return Str::startsWith($method->getName(), 'hook');
            })
            ->map(function (ReflectionMethod $method) {
                return lcfirst(preg_replace('/^hook/', '', $method->getName()));
            });

        //        $availableHooks      = new Collection($this->module->getPossibleHooksList());
        //        $availableHooksNames = $availableHooks->map(function (array $hook) {
        //            return $hook['name'];
        //        })
        //            ->toArray();

        //        $diff = $hooks
        //            ->partition(static function (string $hook) use ($availableHooksNames) {
        //                return in_array($hook, $availableHooksNames, true);
        //            });

        return $hooks->toArray();
    }

    /**
     * @return string
     */
    public function getModuleCountry(): string
    {
        return false === strpos($this->module->name, 'be') ? CountryService::CC_NL : CountryService::CC_BE;
    }

    /**
     * @param  \Cart     $cart
     * @param  float|int $shippingCost
     *
     * @return float|int
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getOrderShippingCost(Cart $cart, $shippingCost)
    {
        $carrierId = (int) $cart->id_carrier;

        if ($this->module->id_carrier !== $carrierId || ! empty($this->context->controller->requestOriginalShippingCost)) {
            return $shippingCost;
        }

        $myParcelCost    = 0;
        $deliveryOptions = Tools::getValue('myparcel-delivery-options', false);

        if ($deliveryOptions) {
            $deliveryOptions = json_decode($deliveryOptions, true);
        } else {
            $deliveryOptions = DeliveryOptionsManager::getFromCart((int) $cart->id);

            if ($deliveryOptions) {
                $deliveryOptions = $deliveryOptions->toArray();
            }
        }

        if (empty($deliveryOptions)) {
            return $shippingCost;
        }

        $isPickup = $deliveryOptions['isPickup'] ?? false;
        if ($isPickup) {
            $myParcelCost += (float) CarrierConfigurationProvider::get(
                $carrierId,
                'pricePickup'
            );
        } else {
            $deliveryType = $deliveryOptions['deliveryType'] ?? AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME;

            if ($deliveryType !== AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME) {
                $priceHourInterval = 'price' . ucfirst($deliveryType) . 'Delivery';
                $myParcelCost      += (float) CarrierConfigurationProvider::get(
                    $carrierId,
                    $priceHourInterval
                );
            }

            if (! empty($deliveryOptions['shipmentOptions']['only_recipient'])) {
                $myParcelCost += (float) CarrierConfigurationProvider::get(
                    $carrierId,
                    'priceOnlyRecipient'
                );
            }

            if (! empty($deliveryOptions['shipmentOptions']['signature'])) {
                $myParcelCost += (float) CarrierConfigurationProvider::get(
                    $carrierId,
                    'priceSignature'
                );
            }
        }

        return $shippingCost + $myParcelCost;
    }
}
