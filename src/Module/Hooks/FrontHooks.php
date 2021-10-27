<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Address;
use Currency;
use Db;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\DeliverySettings\DeliveryOptions;
use Media;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use OrderControllerCore;
use Tools;
use Validate;

trait FrontHooks
{
    /**
     * @param  array $params
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function hookActionCarrierProcess(array $params): void
    {
        $options = Tools::getValue('myparcel-delivery-options');

        if (! $options || '[]' === $options) {
            return;
        }

        /**
         * @var \PrestaShop\PrestaShop\Adapter\Entity\Cart $cart
         */
        $cart = $params['cart'];

        $optionsArray    = json_decode($options, true);
        $deliveryOptions = DeliveryOptionsAdapterFactory::create($optionsArray);

        $action    = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (Tools::isSubmit('confirmDeliveryOption')) {
            $this->saveDeliveryOptions($cart->id, $deliveryOptions);
        }

        if ('selectDeliveryOption' === $action && ! empty($carrierId)) {
            $this->saveDeliveryOptions($cart->id, $deliveryOptions);
        }
    }

    /**
     * @param $params
     *
     * @return false|string
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        $address = new Address($this->context->cart->id_address_delivery);

        if (! Validate::isLoadedObject($address)) {
            return '';
        }

        $address->address1 = preg_replace('/\D/', '', $address->address1);

        if (empty($this->context->cart->id_carrier)) {
            $selectedDeliveryOption          = current($this->context->cart->getDeliveryOption(null, false, false));
            $this->context->cart->id_carrier = (int) $selectedDeliveryOption;
        }

        $this->context->smarty->assign([
            'address'               => $address,
            'delivery_settings'     => DeliveryOptions::queryByCart((int) $this->context->cart->id),
            'shipping_cost'         => $this->getShippingCost($params['carrier'], $address),
            'carrier'               => $params['carrier'],
            'enableDeliveryOptions' => (new PackageTypeCalculator())
                ->allowDeliveryOptions($this->context->cart, $this->getModuleCountry()),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier.tpl');
    }

    public function hookHeader(): void
    {
        if (! $this->context->controller instanceof OrderControllerCore) {
            return;
        }

        $this->context->controller->addCSS($this->_path . 'views/css/myparcel.css');
        $this->context->controller->addJS($this->_path . 'views/dist/js/external/myparcel.js');
        $this->context->controller->addJS($this->_path . 'views/dist/js/frontend.js');

        Media::addJsDefL(
            'myparcel_carrier_init_url',
            $this->context->link->getModuleLink($this->name, 'checkout', [], null, null, null, true)
        );
    }

    /**
     * @param  int                                                                        $cartId
     * @param  \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    protected function saveDeliveryOptions(int $cartId, AbstractDeliveryOptionsAdapter $deliveryOptions): void
    {
        $deliveryOptionsJson = json_encode($deliveryOptions->toArray());

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->insert(
                Table::TABLE_DELIVERY_SETTINGS,
                [
                    'id_cart'           => $cartId,
                    'delivery_settings' => pSQL($deliveryOptionsJson),
                ],
                false,
                true,
                Db::REPLACE
            );
    }

    /**
     * @param                                                    $carrier
     * @param  \Address                                          $address
     *
     * @return float|string
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    private function getShippingCost(
        $carrier,
        Address $address
    ) {
        $translator = $this->context->getTranslator();

        $shippingOptions = $this->getShippingOptions($carrier['id'], $address);
        $includeTax      = $shippingOptions['include_tax'];

        $cost = $includeTax
            ?
            $carrier['price_with_tax']
            :
            $carrier['price_without_tax'];

        $shipping_cost = Tools::displayPrice(
            $cost,
            Currency::getCurrencyInstance((int) $this->context->cart->id_currency)
        );

        if ($shippingOptions['display_tax_label']) {
            $shipping_cost = $translator->trans(
                $includeTax ? '%price% tax incl.' : '%price% tax excl.',
                ['%price%' => $shipping_cost],
                'Shop.Theme.Checkout'
            );
        }
        return $shipping_cost;
    }
}
