<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order as PsOrder;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

/**
 * JSON encodes input if it's an array, merging it with given default values. Otherwise, just returns input.
 *
 * @param  mixed $input
 * @param  array $defaults
 *
 * @return false|mixed|string
 */
function toJsonWithDefaults($input, array $defaults)
{
    if (! is_array($input)) {
        return $input;
    }

    return json_encode(array_replace_recursive($defaults, $input));
}

it('migrates delivery options to pdk', function ($deliverySettings, $extraOptions, array $result) {
    (new FactoryCollection([
        psFactory(PsOrder::class)->withIdCart(20),
    ]))->store();

    MockPsDb::insertRows(AbstractPsMigration::LEGACY_TABLE_DELIVERY_SETTINGS, [
        [
            'id_cart'           => 20,
            'delivery_settings' => toJsonWithDefaults($deliverySettings, [
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
            ]),
            'extra_options'     => toJsonWithDefaults($extraOptions, [
                'labelAmount'        => 1,
                'digitalStampWeight' => 0,
            ]),
        ],
    ], 'id_delivery_setting');

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkDeliveryOptionsMigration $migration */
    $migration = Pdk::get(PdkDeliveryOptionsMigration::class);
    $migration->up();

    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    $order                = $orderRepository->get(1);
    $deliveryOptionsArray = $order->deliveryOptions->toStorableArray();

    $finalShipmentOptions = Utils::filterNull(array_replace([
        ShipmentOptions::AGE_CHECK         => TriStateService::INHERIT,
        ShipmentOptions::DIRECT_RETURN     => TriStateService::INHERIT,
        ShipmentOptions::HIDE_SENDER       => TriStateService::INHERIT,
        ShipmentOptions::INSURANCE         => TriStateService::INHERIT,
        ShipmentOptions::LABEL_DESCRIPTION => TriStateService::INHERIT,
        ShipmentOptions::LARGE_FORMAT      => TriStateService::INHERIT,
        ShipmentOptions::ONLY_RECIPIENT    => TriStateService::INHERIT,
        ShipmentOptions::RECEIPT_CODE      => TriStateService::INHERIT,
        ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::INHERIT,
        ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
        ShipmentOptions::TRACKED           => TriStateService::INHERIT,
    ], $result[DeliveryOptions::SHIPMENT_OPTIONS] ?? []));

    $fullResult = array_replace(
        array_replace([
            DeliveryOptions::CARRIER       => [
                'externalIdentifier' => Platform::get('defaultCarrier'),
            ],
            DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::LABEL_AMOUNT  => 1,
            DeliveryOptions::PACKAGE_TYPE  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ], $result),
        [DeliveryOptions::SHIPMENT_OPTIONS => $finalShipmentOptions]
    );

    expect($deliveryOptionsArray)->toEqual($fullResult);
})
    ->with([
        'defaults' => [
            'delivery_settings' => [
                'carrier' => Carrier::CARRIER_POSTNL_NAME,
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER => ['externalIdentifier' => Carrier::CARRIER_POSTNL_NAME],
            ],
        ],

        'dhlforyou with delivery date' => [
            'delivery_settings' => [
                'carrier'      => 'dhlforyou',
                'date'         => '2077-04-07T00:00:00.000Z',
                'deliveryType' => 'morning',
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER       => ['externalIdentifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
                DeliveryOptions::DATE          => '2077-04-07 00:00:00',
                DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
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
                DeliveryOptions::CARRIER      => ['externalIdentifier' => Carrier::CARRIER_POSTNL_NAME],
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
                DeliveryOptions::CARRIER          => ['externalIdentifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::INSURANCE         => 2000,
                    ShipmentOptions::AGE_CHECK         => TriStateService::ENABLED,
                    ShipmentOptions::HIDE_SENDER       => TriStateService::ENABLED,
                    ShipmentOptions::LARGE_FORMAT      => TriStateService::ENABLED,
                    ShipmentOptions::ONLY_RECIPIENT    => TriStateService::ENABLED,
                    ShipmentOptions::DIRECT_RETURN     => TriStateService::ENABLED,
                    ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::ENABLED,
                    ShipmentOptions::SIGNATURE         => TriStateService::ENABLED,
                    ShipmentOptions::LABEL_DESCRIPTION => 'hello',
                ],
            ],
        ],

        'all shipment options disabled' => [
            'delivery_settings' => [
                'carrier'         => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                'shipmentOptions' => [
                    'signature'         => false,
                    'insurance'         => 0,
                    'age_check'         => false,
                    'only_recipient'    => false,
                    'return'            => false,
                    'same_day_delivery' => false,
                    'large_format'      => false,
                    'label_description' => 'hello',
                    'hide_sender'       => false,
                    'extra_assurance'   => false,
                ],
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER          => ['externalIdentifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::INSURANCE         => 0,
                    ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
                    ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
                    ShipmentOptions::LARGE_FORMAT      => TriStateService::DISABLED,
                    ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
                    ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
                    ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
                    ShipmentOptions::SIGNATURE         => TriStateService::DISABLED,
                    ShipmentOptions::LABEL_DESCRIPTION => 'hello',
                ],
            ],
        ],

        'pickup location' => [
            'delivery_settings' => [
                'carrier'        => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                'pickupLocation' => [
                    'box_number'        => 'box_number',
                    'cc'                => 'cc',
                    'city'              => 'city',
                    'number'            => 'number',
                    'number_suffix'     => 'number_suffix',
                    'postal_code'       => 'postal_code',
                    'region'            => 'region',
                    'state'             => 'state',
                    'street'            => 'street',
                    'location_code'     => 'location_code',
                    'location_name'     => 'location_name',
                    'retail_network_id' => 'retail_network_id',
                ],
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER         => ['externalIdentifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
                DeliveryOptions::PICKUP_LOCATION => [
                    'boxNumber'       => 'box_number',
                    'cc'              => 'cc',
                    'city'            => 'city',
                    'number'          => 'number',
                    'numberSuffix'    => 'number_suffix',
                    'postalCode'      => 'postal_code',
                    'region'          => 'region',
                    'state'           => 'state',
                    'street'          => 'street',
                    'locationCode'    => 'location_code',
                    'locationName'    => 'location_name',
                    'retailNetworkId' => 'retail_network_id',
                ],
            ],
        ],

        'unknown values replaced with defaults' => [
            'delivery_settings' => [
                'carrier'         => 'unknown',
                'deliveryType'    => 'unknown',
                'packageType'     => 'unknown',
                'isPickup'        => 'unknown',
                'shipmentOptions' => 'unknown',
            ],
            'extra_options'     => [
                'labelAmount'        => 'unknown',
                'digitalStampWeight' => 'unknown',
            ],

            // Use defaults but remove label description
            'result'            => [
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::LABEL_DESCRIPTION => null,
                ],
            ],
        ],

        'invalid value in delivery settings' => [
            'delivery_settings' => null,
            'extra_options'     => [],

            // Use defaults but remove label description
            'result'            => [
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::LABEL_DESCRIPTION => null,
                ],
            ],
        ],

        'invalid value in extra options' => [
            'delivery_settings' => [],
            'extra_options'     => false,
            'result'            => [],
        ],
    ]);
