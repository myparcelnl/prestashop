<?php

namespace MyParcelNL\PrestaShop\Migration\Util;

final class ToPackageTypeName extends TransformValue
{
    public function __construct()
    {
        parent::__construct(static function ($value) {
            if (is_numeric($value)) {
                if (in_array((int) $value, DeliveryOptions::PACKAGE_TYPES_IDS, true)) {
                    return array_flip(DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP)[$value];
                }

                return DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
            }

            return in_array($value, DeliveryOptions::PACKAGE_TYPES_NAMES, true)
                ? $value
                : DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
        });
    }
}
