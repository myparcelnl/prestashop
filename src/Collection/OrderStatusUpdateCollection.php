<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Collection;

use MyParcelNL\Sdk\src\Support\Collection;

class OrderStatusUpdateCollection extends Collection
{
    /**
     * @var \MyParcelNL\PrestaShop\Entity\OrderStatus\OrderStatusUpdateInterface[]
     */
    protected $items;
}
