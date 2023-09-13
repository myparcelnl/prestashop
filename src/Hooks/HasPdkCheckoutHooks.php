<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsCarrierAdapter;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use Throwable;
use Tools;

/**
 * @property \Context $context
 */
trait HasPdkCheckoutHooks
{
    /**
     * @param  array{cart: \Cart} $params
     *
     * @return void
     * @throws \Exception
     */
    public function hookActionCarrierProcess(array $params): void
    {
        $action    = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (('selectDeliveryOption' !== $action || empty($carrierId)) && ! Tools::isSubmit('confirmDeliveryOption')) {
            return;
        }

        $options = Tools::getValue(Pdk::get('checkoutHiddenInputName'));

        if (! $options || '[]' === $options) {
            return;
        }

        $cartId = $params['cart']->id ?? null;

        try {
            $pdkOrder = new PdkOrder(['deliveryOptions' => json_decode($options, true)]);

            $this->saveOrderData($cartId, $pdkOrder);
        } catch (Throwable $e) {
            Logger::error(
                'Failed to save order data',
                [
                    'exception' => $e,
                    'options'   => $options,
                    'cartId'    => $cartId,
                ]
            );
        }
    }

    /**
     * @param $params
     *
     * @return false|string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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

        //        $carrierName     = $carrierAdapter->getCarrierName($params['carrier']['id']);
        //        $carrier         = new Carrier(['name' => $carrierName]);

        $shippingAddress = $addressAdapter->fromAddress($this->context->cart->id_address_delivery);
        $billingAddress  = $addressAdapter->fromAddress($this->context->cart->id_address_invoice);
        $deliveryOptions = Frontend::renderDeliveryOptions($cartRepository->get($this->context->cart));

        $this->context->smarty->setEscapeHtml(false);

        $this->context->smarty->assign([
            'deliveryOptions' => $deliveryOptions,
            'shippingAddress' => $this->encodeAddress($shippingAddress),
            'billingAddress'  => $this->encodeAddress($billingAddress),
            //            'carrier'         => $carrierName,
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier.tpl');
    }

    /**
     * @param  array $address
     *
     * @return string
     */
    private function encodeAddress(array $address): string
    {
        return htmlspecialchars(json_encode(Utils::filterNull($address)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param  int                                      $cartId
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function saveOrderData(int $cartId, PdkOrder $order): void
    {
        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        $cartDeliveryOptionsRepository->updateOrCreate(
            [
                'idCart' => $cartId,
            ],
            [
                'data' => json_encode($order->toStorableArray()),
            ]
        );
    }
}
