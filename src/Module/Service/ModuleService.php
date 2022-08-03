<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Service;

use Cart;
use Context;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsManager;
use Gett\MyparcelBE\Module\Configuration\SettingsMenu;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use MyParcelBE;
use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
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
     * @var \MyParcelBE
     */
    private $module;

    /**
     * @param  \MyParcelBE $module
     */
    public function __construct(MyParcelBE $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $configuration = new SettingsMenu($this->module);

        $this->context->smarty->assign([
            'menutabs' => $configuration->initNavigation(),
            'ajaxUrl'  => $this->module->getBaseUrl(true),
        ]);

        $this->context->smarty->assign('module_dir', $this->module->getPathUri());
        $output = $this->module->display($this->module->getLocalPath(), 'views/templates/admin/navbar.tpl');

        return $output . $configuration->renderMenu((int) Tools::getValue('menu') ?: 0);
    }

    /**
     * @return array
     */
    public function getHooks(): array
    {
        $reflectionClass = new ReflectionClass(MyParcelBE::class);

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

    /**
     * @return bool
     * @deprecated
     */
    public function isBE(): bool
    {
        return 'BE' === $this->getModuleCountry();
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isNL(): bool
    {
        return 'NL' === $this->getModuleCountry();
    }
}
