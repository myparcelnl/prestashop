<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

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
            CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
            CarrierSettings::ALLOW_PICKUP_LOCATIONS   => true,
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
            CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
        ],
        'result'   => true,
    ],

    'enabled in checkout and carrier, with only pickup' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            CarrierSettings::ALLOW_PICKUP_LOCATIONS   => true,
        ],
        'result'   => true,
    ],

    'enabled in checkout and carrier, with both delivery and pickup' => [
        'settings' => [
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => true,
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
            CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
            CarrierSettings::ALLOW_PICKUP_LOCATIONS   => true,
        ],
        'result'   => true,
    ],
]);
