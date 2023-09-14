<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Cart;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Facade\EntityManager;
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
        $action = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (('selectDeliveryOption' !== $action || empty($carrierId)) && ! Tools::isSubmit('confirmDeliveryOption')) {
            return;
        }

        $this->saveDeliveryOptionsToCart($params['cart']);
    }

    /**
     * @param  \Cart $cart
     *
     * @return void
     */
    private function saveDeliveryOptionsToCart(Cart $cart): void
    {
        $options = Tools::getValue(Pdk::get('checkoutHiddenInputName'));

        if (! $options || '[]' === $options) {
            return;
        }

        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        try {
            $deliveryOptions = new DeliveryOptions(json_decode($options, true));

            $cartDeliveryOptionsRepository->updateOrCreate(
                [
                    'cartId' => $cart->id,
                ],
                [
                    'data' => json_encode($deliveryOptions->toStorableArray()),
                ]
            );

            EntityManager::flush();
        } catch (Throwable $e) {
            Logger::error('Failed to save delivery options to cart', [
                'exception' => $e,
                'options'   => $options,
                'cartId'    => $cart->id,
            ]);
        }
    }
}
