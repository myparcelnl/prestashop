<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Address;
use Media;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\DeliveryOptions\DeliveryOptionsManager;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Facade\Twig;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Repository\PsCarrierConfigurationRepository;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\ShippingOptions;
use MyParcelNL\Sdk\src\Support\Arr;
use OrderControllerCore;
use Tools;
use Validate;

trait HasFrontendHooks
{
    /**
     * Run on choosing shipping method in checkout.
     *
     * @param  array $params
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     * @noinspection PhpUnused
     */
    public function hookActionCarrierProcess(array $params): void
    {
        $options = Tools::getValue('myparcel-delivery-options');

        if (! $options || '[]' === $options) {
            return;
        }

        $action    = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (('selectDeliveryOption' === $action && ! empty($carrierId)) || Tools::isSubmit('confirmDeliveryOption')) {
            /** @var \PrestaShop\PrestaShop\Adapter\Entity\Cart $cart */
            $cart = $params['cart'];



            /** @var PsCartDeliveryOptionsRepository $repository */
            $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

            $entity = $repository->createEntity();

            $repository->save($entity);

            $optionsArray    = json_decode($options, true);
            $deliveryOptions = new DeliveryOptions();
            $deliveryOptions->fill(Arr::only($optionsArray, array_keys($deliveryOptions->getAttributes())));

            DeliveryOptionsManager::save($cart->id, $deliveryOptions);
        }
    }

    /**
     * @param  array $params
     *
     * @return string
     * @throws \Exception
     */
    public function hookDisplayCarrierExtraContent(array $params): string
    {
        $address = new Address($this->context->cart->id_address_delivery);

        if (! Validate::isLoadedObject($address)) {
            return '';
        }

        /** @var PsCarrierConfigurationRepository $repository */
        $repository           = Pdk::get(PsCarrierConfigurationRepository::class);
        $carrierConfiguration = $repository->findOneBy(['idCarrier' => $params['carrier']['id']]);

        if (! $carrierConfiguration) {
            return '';
        }

        $address->address1 = preg_replace('/\D/', '', $address->address1);

        if (empty($this->context->cart->id_carrier)) {
            $selectedDeliveryOption          = current($this->context->cart->getDeliveryOption(null, false, false));
            $this->context->cart->id_carrier = (int) $selectedDeliveryOption;
        }

        $this->context->smarty->assign([
            'address'               => $address,
            'shipping_cost'         => $this->getShippingCost($params['carrier'], $address),
            'carrier'               => $params['carrier'],
            'enableDeliveryOptions' => (new PackageTypeCalculator())
                ->deliveryOptionsAllowed($this->context->cart, ModuleService::getModuleCountry()),
        ]);
        //

        return $this->display($this->name, 'views/templates/hook/carrier.tpl');
    }

    /**
     * Load the CSS and JS of the frontend app on the order page.
     *
     * @return void
     */
    public function hookDisplayHeader(): void
    {
        if (! $this->context->controller instanceof OrderControllerCore) {
            return;
        }

        $this->context->controller->addJS("{$this->_path}views/js/frontend/lib/prestashop-frontend.js");
        $this->context->controller->addCSS("{$this->_path}views/js/frontend/lib/style.css");

        Media::addJsDefL(
            'myparcel_delivery_options_url',
            $this->context->link->getModuleLink($this->name, 'checkout', [], null, null, null, true)
        );
    }

    /**
     * @param                                                    $carrier
     * @param  \Address                                          $address
     *
     * @return string
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     * @throws \Exception
     */
    private function getShippingCost(
        $carrier,
        Address $address
    ): string {
        $translator = $this->context->getTranslator();
        $locale     = $this->context->getCurrentLocale();

        if (! $translator || ! $locale) {
            throw new \RuntimeException('Context not initialized');
        }

        $class           = Pdk::get(ShippingOptions::class);
        $shippingOptions = $class->get($carrier['id'], $address);
        $includeTax      = $shippingOptions['include_tax'];

        $cost = $includeTax
            ?
            $carrier['price_with_tax']
            :
            $carrier['price_without_tax'];

        $shippingCost = $locale->formatPrice($cost, \Currency::getIsoCodeById((int) $this->context->cart->id_currency));

        if ($shippingOptions['display_tax_label']) {
            $shippingCost = $translator->trans(
                $includeTax ? '%price% tax incl.' : '%price% tax excl.',
                ['%price%' => $shippingCost],
                'Shop.Theme.Checkout'
            );
        }
        return $shippingCost;
    }
}
