<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use Order;

/**
 * @template T of \Order
 * @extends PsSpecificObjectModelServiceInterface<T>
 */
interface PsOrderServiceInterface extends PsSpecificObjectModelServiceInterface
{
    /**
     * In PrestaShop, the delivery options are stored in the cart, not in the order. So we need to get them from the
     * cart if they are not present in the order yet.
     *
     * @param  string|int|T $input
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderData($input): array;

    /**
     * @param  string|int|Order $input
     * @param  array            $orderData
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateOrderData($input, array $orderData): void;
}
