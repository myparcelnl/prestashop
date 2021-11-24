<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;

class DeliveryOptionsFromOrderAdapter extends AbstractDeliveryOptionsAdapter
{
    /**
     * @param  array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        $this->carrier         = $data['carrier'] ?? null;
        $this->date            = $data['date'] ?? null;
        $this->deliveryType    = $data['deliveryType'] ?? null;
        $this->packageType     = (new PackageTypeCalculator())->convertToName($data['packageType']);
        $this->shipmentOptions = new ShipmentOptionsFromOrderAdapter($data);
    }
}
