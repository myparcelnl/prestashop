<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use Order;

/**
 * @template T of Order
 * @extends \MyParcelNL\PrestaShop\Service\PsSpecificObjectModelService<T>
 */
final class PsOrderService extends PsSpecificObjectModelService implements PsOrderServiceInterface
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository
     */
    private $psCartDeliveryOptionsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $psOrderDataRepository;

    /**
     * @param  \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface     $psObjectModelService
     * @param  \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository           $psOrderDataRepository
     */
    public function __construct(
        PsObjectModelServiceInterface   $psObjectModelService,
        PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository,
        PsOrderDataRepository           $psOrderDataRepository
    ) {
        parent::__construct($psObjectModelService);
        $this->psCartDeliveryOptionsRepository = $psCartDeliveryOptionsRepository;
        $this->psOrderDataRepository           = $psOrderDataRepository;
    }

    /**
     * In PrestaShop, the delivery options are stored in the cart, not in the order. So we need to get them from the
     * cart if they are not present in the order yet.
     *
     * @param  string|int|Order $input
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function getOrderData($input): array
    {
        if (! $this->exists($input)) {
            return [];
        }

        $fromOrder = $this->psOrderDataRepository->findOneBy(['orderId' => $this->getId($input)]);

        if (! $fromOrder) {
            return $this->getFromCart($input);
        }

        return $fromOrder->getData();
    }

    /**
     * @param  string|int|Order $input
     * @param  array            $orderData
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateOrderData($input, array $orderData): void
    {
        $this->psOrderDataRepository->updateOrCreate(
            ['orderId' => $this->getId($input)],
            ['data' => json_encode($orderData)]
        );
    }

    protected function getClass(): string
    {
        return Order::class;
    }

    /**
     * Get the delivery options from the cart and save it to the order.
     *
     * @param  string|int|Order $input
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    private function getFromCart($input): array
    {
        /** @var Order $order */
        $order    = $this->get($input);
        $id       = $this->getId($order);
        $fromCart = $this->psCartDeliveryOptionsRepository->findOneBy(['cartId' => $order->id_cart]);

        if ($fromCart) {
            Logger::debug("[Order $id] Delivery options found in cart, saving to order");
        } else {
            Logger::debug("[Order $id] Saving empty order data to order");
        }

        $orderData = $fromCart ? ['deliveryOptions' => $fromCart->getData()] : [];

        $this->updateOrderData($order, $orderData);

        EntityManager::flush();

        return $orderData;
    }
}
