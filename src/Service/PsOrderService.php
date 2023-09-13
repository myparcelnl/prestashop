<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use Order;

final class PsOrderService extends Repository implements PsOrderServiceInterface
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
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage                        $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository           $psOrderDataRepository
     */
    public function __construct(
        MemoryCacheStorage              $storage,
        PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository,
        PsOrderDataRepository           $psOrderDataRepository
    ) {
        parent::__construct($storage);
        $this->psCartDeliveryOptionsRepository = $psCartDeliveryOptionsRepository;
        $this->psOrderDataRepository           = $psOrderDataRepository;
    }

    /**
     * @param  string|int|Order $input
     *
     * @return \Order
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function get($input): Order
    {
        if ($input instanceof Order) {
            return $input;
        }

        return new Order((int) $input);
    }

    /**
     * In PrestaShop, the delivery options are stored in the cart, not in the order. So we need to get them from the
     * cart if they are not present in the order yet.
     *
     * @param  string|int|Order $orderOrId
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderData($orderOrId): array
    {
        $fromOrder = $this->psOrderDataRepository->findOneBy(['idOrder' => $this->getOrderId($orderOrId)]);

        if (! $fromOrder) {
            return $this->getFromCart($this->get($orderOrId));
        }

        return $fromOrder->getData();
    }

    /**
     * @param  string|int|Order $orderOrId
     * @param  array            $orderData
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateData($orderOrId, array $orderData): void
    {
        $this->psOrderDataRepository->updateOrCreate(
            ['idOrder' => $this->getOrderId($orderOrId)],
            ['data' => json_encode($orderData)]
        );
    }

    /**
     * Get the delivery options from the cart and save it to the order.
     *
     * @param  \Order $order
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    private function getFromCart(Order $order): array
    {
        $fromCart = $this->psCartDeliveryOptionsRepository->findOneBy(['idCart' => $order->id_cart]);

        $context = [
            'cartId'  => $order->id_cart,
            'orderId' => $order->id,
        ];

        if ($fromCart) {
            Logger::debug('Delivery options found in cart, saving to order', $context);
        } else {
            Logger::debug('No delivery options found in cart, saving empty order data to order', $context);
        }

        $deliveryOptions = $fromCart ? $fromCart->getData() : [];

        $this->updateData((string) $order->id, $deliveryOptions);

        return $deliveryOptions;
    }

    /**
     * @param  string|int|Order $orderOrId
     *
     * @return string
     */
    private function getOrderId($orderOrId): string
    {
        if ($orderOrId instanceof Order) {
            return (string) $orderOrId->id;
        }

        return $orderOrId;
    }
}
