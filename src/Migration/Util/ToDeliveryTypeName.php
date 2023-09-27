<?php

namespace MyParcelNL\PrestaShop\Migration\Util;

final class ToDeliveryTypeName extends TransformValue
{
    public function __construct()
    {
        parent::__construct([$this, 'convert']);
    }

    /**
     * @param  mixed $value
     *
     * @return string
     */
    protected function convert($value): string
    {
        if (is_numeric($value)) {
            if (in_array((int) $value, DeliveryOptions::DELIVERY_TYPES_IDS, true)) {
                return array_flip(DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP)[$value];
            }

            return DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME;
        }

        return in_array($value, DeliveryOptions::DELIVERY_TYPES_NAMES, true)
            ? $value
            : DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME;
    }
}
