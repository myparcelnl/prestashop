<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use Order;

interface PsOrderServiceInterface
{
    /**
     * @param  string|int|Order $orderOrId
     *
     * @return \Order
     */
    public function get($orderOrId): Order;

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
    public function getOrderData($orderOrId): array;

    /**
     * @param  string|int|Order $orderOrId
     * @param  array            $orderData
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateData($orderOrId, array $orderData): void;
}
