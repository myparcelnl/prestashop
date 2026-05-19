<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CollectDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CooledDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FreshFoodDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FrozenDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\ReceiptCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order as PsOrder;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function MyParcelNL\PrestaShop\setupAccountAndCarriers;

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)->fromPostNL(),
            factory(Carrier::class)->fromDhlForYou()
        )
    );
});

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
        (new AgeCheckDefinition())->getShipmentOptionsKey()            => TriStateService::INHERIT,
        (new DirectReturnDefinition())->getShipmentOptionsKey()        => TriStateService::INHERIT,
        (new HideSenderDefinition())->getShipmentOptionsKey()          => TriStateService::INHERIT,
        (new InsuranceDefinition())->getShipmentOptionsKey()           => TriStateService::INHERIT,
        ShipmentOptions::LABEL_DESCRIPTION                             => TriStateService::INHERIT,
        (new LargeFormatDefinition())->getShipmentOptionsKey()         => TriStateService::INHERIT,
        (new OnlyRecipientDefinition())->getShipmentOptionsKey()       => TriStateService::INHERIT,
        (new ReceiptCodeDefinition())->getShipmentOptionsKey()         => TriStateService::INHERIT,
        (new SameDayDeliveryDefinition())->getShipmentOptionsKey()     => TriStateService::INHERIT,
        (new SignatureDefinition())->getShipmentOptionsKey()           => TriStateService::INHERIT,
        (new TrackedDefinition())->getShipmentOptionsKey()             => TriStateService::INHERIT,
        (new CollectDefinition())->getShipmentOptionsKey()             => TriStateService::INHERIT,
        (new FreshFoodDefinition())->getShipmentOptionsKey()           => TriStateService::INHERIT,
        (new FrozenDefinition())->getShipmentOptionsKey()              => TriStateService::INHERIT,
        (new PriorityDeliveryDefinition())->getShipmentOptionsKey()    => TriStateService::INHERIT,
        (new SaturdayDeliveryDefinition())->getShipmentOptionsKey()    => TriStateService::INHERIT,
        (new CooledDeliveryDefinition())->getShipmentOptionsKey()      => TriStateService::INHERIT,
    ], $result[DeliveryOptions::SHIPMENT_OPTIONS] ?? []));

    $fullResult = array_replace(
        array_replace([
            DeliveryOptions::CARRIER       => 'POSTNL',
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
                'carrier' => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            ],
            'extra_options'     => [],

            'result' => [
                DeliveryOptions::CARRIER => 'POSTNL',
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
                DeliveryOptions::CARRIER       => 'DHL_FOR_YOU',
                DeliveryOptions::DATE          => '2077-04-07 00:00:00',
                DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            ],
        ],

        'different label amount' => [
            'delivery_settings' => [
                'carrier' => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            ],
            'extra_options'     => [
                'labelAmount' => 5,
            ],

            'result' => [
                DeliveryOptions::CARRIER      => 'POSTNL',
                DeliveryOptions::LABEL_AMOUNT => 5,
            ],
        ],

        'all shipment options enabled' => [
            'delivery_settings' => [
                'carrier'         => Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
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
                DeliveryOptions::CARRIER          => 'DHL_FOR_YOU',
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    (new InsuranceDefinition())->getShipmentOptionsKey()       => 2000,
                    (new AgeCheckDefinition())->getShipmentOptionsKey()        => TriStateService::ENABLED,
                    (new HideSenderDefinition())->getShipmentOptionsKey()      => TriStateService::ENABLED,
                    (new LargeFormatDefinition())->getShipmentOptionsKey()     => TriStateService::ENABLED,
                    (new OnlyRecipientDefinition())->getShipmentOptionsKey()   => TriStateService::ENABLED,
                    (new DirectReturnDefinition())->getShipmentOptionsKey()    => TriStateService::ENABLED,
                    (new SameDayDeliveryDefinition())->getShipmentOptionsKey() => TriStateService::ENABLED,
                    (new SignatureDefinition())->getShipmentOptionsKey()       => TriStateService::ENABLED,
                    ShipmentOptions::LABEL_DESCRIPTION                         => 'hello',
                ],
            ],
        ],

        'all shipment options disabled' => [
            'delivery_settings' => [
                'carrier'         => Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
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
                DeliveryOptions::CARRIER          => 'DHL_FOR_YOU',
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    (new InsuranceDefinition())->getShipmentOptionsKey()       => 0,
                    (new AgeCheckDefinition())->getShipmentOptionsKey()        => TriStateService::DISABLED,
                    (new HideSenderDefinition())->getShipmentOptionsKey()      => TriStateService::DISABLED,
                    (new LargeFormatDefinition())->getShipmentOptionsKey()     => TriStateService::DISABLED,
                    (new OnlyRecipientDefinition())->getShipmentOptionsKey()   => TriStateService::DISABLED,
                    (new DirectReturnDefinition())->getShipmentOptionsKey()    => TriStateService::DISABLED,
                    (new SameDayDeliveryDefinition())->getShipmentOptionsKey() => TriStateService::DISABLED,
                    (new SignatureDefinition())->getShipmentOptionsKey()       => TriStateService::DISABLED,
                    ShipmentOptions::LABEL_DESCRIPTION                         => 'hello',
                ],
            ],
        ],

        'pickup location' => [
            'delivery_settings' => [
                'carrier'        => Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
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
                DeliveryOptions::CARRIER         => 'DHL_FOR_YOU',
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
