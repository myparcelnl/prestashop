<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity;

interface OrderStatusUpdateInterface
{
    /**
     * @return string
     */
    public function getOrderStatusSetting(): string;

    /**
     * Logic that should be run when this update is applied.
     */
    public function onExecute(): void;
}
