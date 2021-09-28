<?php

declare(strict_types=1);

namespace Gett\MyparcelBE;

use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;

class Order extends \Order
{
    /**
     * @throws \Exception
     */
    public function getDeliverySettings(): AbstractDeliveryOptionsAdapter
    {
        return DeliveryOptions::getFromOrder($this);
    }

    /**
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function getFields(): array
    {
        $fields                      = parent::getFields();
        $fields['delivery_settings'] = $this->getDeliverySettings()->toArray();

        return $fields;
    }
}
