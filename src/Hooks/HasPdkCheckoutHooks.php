<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
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
                'cartId' => $cartId,
            ],
            [
                'data' => json_encode($order->toStorableArray()),
            ]
        );
    }
}
