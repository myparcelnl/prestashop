<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module;

use Context;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\Module\Hooks\ModuleSettingsRenderService;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use MyParcelBE;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

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

    public function __construct(MyParcelBE $module, Context $context)
    {
        $this->module  = $module;
        $this->context = $context;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        return ModuleSettingsRenderService::getInstance()->renderModuleSettings();

//        $configuration = new SettingsMenu($this->module);
//
//        $this->context->smarty->assign([
//            'menutabs' => $configuration->initNavigation(),
//            'ajaxUrl'  => $this->module->getBaseUrl(true),
//        ]);
//
//        $this->context->smarty->assign('module_dir', $this->module->getPathUri());
//        $output = $this->module->display($this->module->getLocalPath(), 'views/templates/admin/navbar.tpl');
//
//        return $output . $configuration->renderMenu((int) Tools::getValue('menu') ?: 0);
    }

    /**
     * @param $cart
     * @param $shippingCost
     *
     * @return float|int
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getOrderShippingCost($cart, $shippingCost)
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
            $deliveryOptions = DeliveryOptions::getFromCart((int) $cart->id);

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
