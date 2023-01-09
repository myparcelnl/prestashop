<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\OrderStatus;

interface OrderStatusUpdateInterface
{
    /**
     * @return string
     */
    public function getOrderStatusSetting(): string;

    /**
     * Logic that should be run when this update is applied. Returns success
     * status as boolean.
     */
    public function onExecute(): bool;
}
