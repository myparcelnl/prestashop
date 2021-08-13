<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Collection;

use MyParcelNL\Sdk\src\Support\Collection;

class OrderStatusUpdateCollection extends Collection
{
    /**
     * @var \Gett\MyparcelBE\Entity\OrderStatusUpdateInterface[]
     */
    protected $items;
}
