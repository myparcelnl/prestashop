<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

final class ToDeliveryTypeName extends TransformValue
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param  mixed $defaultValue
     */
    public function __construct($defaultValue = DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME)
    {
        parent::__construct([$this, 'convert']);
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param  mixed $value
     *
     * @return string
     */
    protected function convert($value): string
    {
        return Utils::convertToName($value, DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP) ?? $this->defaultValue;
    }
}
