<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Db;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsCarrierAdapter;
use Tools;

/**
 * @property \Context $context
 */
trait HasPdkCheckoutHooks
{
    /**
     * @param  array $params
     *
     * @return void
     * @throws \Exception
     */
    public function hookActionCarrierProcess(array $params): void
    {
        $options = Tools::getValue(Pdk::get('checkoutHiddenInputName'));

        if (! $options || '[]' === $options) {
            return;
        }

        /**
         * @var \PrestaShop\PrestaShop\Adapter\Entity\Cart $cart
         */
        $cart = $params['cart'];

        $optionsArray    = json_decode($options, true);
        $deliveryOptions = $this->createDeliveryOptions($optionsArray);

        $action    = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (('selectDeliveryOption' === $action && ! empty($carrierId)) || Tools::isSubmit('confirmDeliveryOption')) {
            $this->saveDeliveryOptions($cart->id, $deliveryOptions);
        }
    }

    /**
     * @param $params
     *
     * @return false|string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        /** @var PdkCartRepositoryInterface $cartRepository */
        $cartRepository = Pdk::get(PdkCartRepositoryInterface::class);
        /** @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter $addressAdapter */
        $addressAdapter = Pdk::get(PsAddressAdapter::class);
        /** @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsCarrierAdapter $carrierAdapter */
        $carrierAdapter = Pdk::get(PsCarrierAdapter::class);

        if (empty($this->context->cart->id_carrier)) {
            $selectedDeliveryOption          = current($this->context->cart->getDeliveryOption(null, false, false));
            $this->context->cart->id_carrier = (int) $selectedDeliveryOption;
        }

        $shippingAddress = $addressAdapter->fromAddress($this->context->cart->id_address_delivery);
        $billingAddress  = $addressAdapter->fromAddress($this->context->cart->id_address_invoice);

        $this->context->smarty->setEscapeHtml(false);

        $this->context->smarty->assign([
            'deliveryOptions' => Frontend::renderDeliveryOptions($cartRepository->get($this->context->cart)),
            'shippingAddress' => $this->encodeAddress($shippingAddress),
            'billingAddress'  => $this->encodeAddress($billingAddress),
            'carrier'         => $carrierAdapter->getCarrierName((int) $this->context->cart->id_carrier),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier.tpl');
    }

    /**
     * @param  array $deliveryOptions
     *
     * @return array
     */
    private function createDeliveryOptions(array $deliveryOptions): array
    {
        return [
            'carrier'         => $deliveryOptions['carrier'] ?? null,
            'date'            => $deliveryOptions['date'] ?? null,
            'pickupLocation'  => null,
            'shipmentOptions' => $deliveryOptions['shipmentOptions'] ?? null,
            'deliveryType'    => $deliveryOptions['deliveryType'] ?? null,
            'packageType'     => $deliveryOptions['packageType'] ?? null,
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\ContactDetails $contactDetails
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeAddress(ContactDetails $contactDetails): string
    {
        return htmlspecialchars(
            json_encode(array_filter($contactDetails->toArray())),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * @param  int   $cartId
     * @param  array $deliveryOptions
     *
     * @return void
     * @todo do this using entities
     */
    private function saveDeliveryOptions(int $cartId, array $deliveryOptions): void
    {
        $values = [
            'id_cart'          => $cartId,
            'delivery_options' => pSQL(json_encode($deliveryOptions)),
        ];

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->update(
                'myparcelnl_delivery_options',
                $values,
                false,
                true,
                Db::REPLACE
            );
    }
}
