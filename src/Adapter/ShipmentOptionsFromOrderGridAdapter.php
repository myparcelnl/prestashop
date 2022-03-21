<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractShipmentOptionsAdapter;

class ShipmentOptionsFromOrderGridAdapter extends AbstractShipmentOptionsAdapter
{
    private const DEFAULT_INSURANCE = 0;

    /**
     * @param  array $inputData
     */
    public function __construct(array $inputData)
    {
        $options              = $inputData ?? [];
        $this->signature      = $options['signature'] === 'true' ?? false;
        $this->only_recipient = ($options['only_recipient'] === 'true' ?? false);
        $this->large_format   = ($options['large_format'] === 'true' ?? false);
        $this->age_check      = ($options['age_check'] === 'true' ?? false);
        $this->return         = ($options['return'] === 'true' ?? false);
        $this->insurance      = (int) ($options['insurance'] ?? self::DEFAULT_INSURANCE);
    }
}
