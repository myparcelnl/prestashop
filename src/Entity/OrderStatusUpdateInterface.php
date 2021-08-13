<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity;

interface OrderStatusUpdateInterface
{
    /**
     * The new status the order should get when this update is applied.
     *
     * @return int|null
     */
    public function getNewOrderStatus(): ?int;

    /**
     * Logic that should be run when this update is applied.
     */
    public function onExecute(): void;
}
