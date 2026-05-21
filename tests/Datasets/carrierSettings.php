<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

$allowDeliveryOptionsKey  = SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME);
$allowPickupLocationsKey  = SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP);

dataset('carrierActiveSettings', [
    'all false' => [
        'settings' => [],
        'result'   => false,
    ],

    'only enabled in checkout' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
        ],
        'result'   => false,
    ],

    'only enabled in carrier' => [
        'settings' => [
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            $allowDeliveryOptionsKey                  => true,
            $allowPickupLocationsKey                  => true,
        ],
        'result'   => false,
    ],

    'enabled in checkout and carrier, but no delivery or pickup' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
        ],
        'result'   => false,
    ],

    'enabled in checkout and carrier, with only delivery' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            $allowDeliveryOptionsKey                  => true,
        ],
        'result'   => true,
    ],

    'enabled in checkout and carrier, with only pickup' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            $allowPickupLocationsKey                  => true,
        ],
        'result'   => true,
    ],

    'enabled in checkout and carrier, with both delivery and pickup' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            $allowDeliveryOptionsKey                  => true,
            $allowPickupLocationsKey                  => true,
        ],
        'result'   => true,
    ],
]);
