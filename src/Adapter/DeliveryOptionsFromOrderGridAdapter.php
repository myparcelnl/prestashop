<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;

class DeliveryOptionsFromOrderGridAdapter extends DeliveryOptionsV3Adapter
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->shipmentOptions = new ShipmentOptionsFromOrderGridAdapter($data['shipmentOptions']);
    }
}
