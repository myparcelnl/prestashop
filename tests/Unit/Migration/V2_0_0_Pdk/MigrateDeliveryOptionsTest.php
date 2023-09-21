<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\V2_0_0_Pdk;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use MyParcelNL\PrestaShop\Migration\Migration2_0_0;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order as PsOrder;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('migrates delivery options', function (array $deliverySettings, array $extraOptions, array $result) {
    (new FactoryCollection([
        psFactory(PsOrder::class)->withIdCart(20),
    ]))->store();

    MockPsDb::insertRows(AbstractLegacyPsMigration::LEGACY_TABLE_DELIVERY_SETTINGS, [
        [
            'id_cart'           => 20,
            'delivery_settings' => json_encode(
                array_replace_recursive([
                    'carrier'         => '',
                    'date'            => '',
                    'deliveryType'    => 'standard',
                    'packageType'     => 'package',
                    'isPickup'        => false,
                    'pickupLocation'  => null,
                    'shipmentOptions' => [
                        'age_check'         => null,
                        'extra_assurance'   => null,
                        'hide_sender'       => null,
                        'insurance'         => null,
                        'label_description' => null,
                        'large_format'      => null,
                        'only_recipient'    => null,
                        'return'            => null,
                        'same_day_delivery' => null,
                        'signature'         => null,
                    ],
                ], $deliverySettings)
            ),
            'extra_options'     => json_encode(
                array_replace([
                    'labelAmount'        => 1,
                    'digitalStampWeight' => 0,
                ], $extraOptions)
            ),
        ],
    ], 'id_delivery_setting');

    /** @var \MyParcelNL\PrestaShop\Migration\Migration2_0_0 $migration */
    $migration = Pdk::get(Migration2_0_0::class);
    $migration->up();

    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    $order                = $orderRepository->get(1);
    $deliveryOptionsArray = $order->deliveryOptions->toStorableArray();

    expect($deliveryOptionsArray)->toEqual(
        array_replace_recursive([
            DeliveryOptions::CARRIER          => Platform::get('defaultCarrier'),
            DeliveryOptions::PACKAGE_TYPE     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            DeliveryOptions::DELIVERY_TYPE    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::LABEL_AMOUNT     => 1,
            DeliveryOptions::SHIPMENT_OPTIONS => [
                ShipmentOptions::AGE_CHECK         => TriStateService::INHERIT,
                ShipmentOptions::DIRECT_RETURN     => TriStateService::INHERIT,
                ShipmentOptions::HIDE_SENDER       => TriStateService::INHERIT,
                ShipmentOptions::INSURANCE         => TriStateService::INHERIT,
                ShipmentOptions::LABEL_DESCRIPTION => TriStateService::INHERIT,
                ShipmentOptions::LARGE_FORMAT      => TriStateService::INHERIT,
                ShipmentOptions::ONLY_RECIPIENT    => TriStateService::INHERIT,
                ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::INHERIT,
                ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
            ],
        ], $result)
    );
})
    ->with([
        'defaults' => [
            'delivery_settings' => [
                'carrier' => Carrier::CARRIER_POSTNL_NAME,
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER => Carrier::CARRIER_POSTNL_NAME,
            ],
        ],

        'dhlforyou with delivery date' => [
            'delivery_settings' => [
                'carrier' => 'dhlforyou',
                'date'    => '2023-04-07T00:00:00.000Z',
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                DeliveryOptions::DATE    => '2023-04-07T00:00:00.000Z',
            ],
        ],

        'different label amount' => [
            'delivery_settings' => [
                'carrier' => Carrier::CARRIER_POSTNL_NAME,
            ],
            'extra_options'     => [
                'labelAmount' => 5,
            ],

            'result' => [
                DeliveryOptions::CARRIER      => Carrier::CARRIER_POSTNL_NAME,
                DeliveryOptions::LABEL_AMOUNT => 5,
            ],
        ],

        'all shipment options enabled' => [
            'delivery_settings' => [
                'carrier'         => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                'shipmentOptions' => [
                    'signature'         => true,
                    'insurance'         => 2000,
                    'age_check'         => true,
                    'only_recipient'    => true,
                    'return'            => true,
                    'same_day_delivery' => true,
                    'large_format'      => true,
                    'label_description' => 'hello',
                    'hide_sender'       => true,
                    'extra_assurance'   => true,
                ],
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER          => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::LABEL_DESCRIPTION => 'hello',
                    ShipmentOptions::INSURANCE         => 2000,
                    ShipmentOptions::AGE_CHECK         => true,
                    ShipmentOptions::HIDE_SENDER       => true,
                    ShipmentOptions::LARGE_FORMAT      => true,
                    ShipmentOptions::ONLY_RECIPIENT    => true,
                    ShipmentOptions::DIRECT_RETURN     => true,
                    ShipmentOptions::SAME_DAY_DELIVERY => true,
                    ShipmentOptions::SIGNATURE         => true,
                ],
            ],
        ],
    ])
    ->skip();
