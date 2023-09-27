<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use DateTime;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

it('migrates order shipments to pdk', function (array $orderLabels) {
    /** @var PdkOrderRepositoryInterface $pdkOrderRepository */
    $pdkOrderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    /** @var PsOrderShipmentRepository $psOrderShipmentRepository */
    $psOrderShipmentRepository = Pdk::get(PsOrderShipmentRepository::class);

    psFactory(Order::class)
        ->withIdCart(20)
        ->store();

    MockPsDb::insertRows(AbstractLegacyPsMigration::LEGACY_TABLE_DELIVERY_SETTINGS, [
        [
            'id_cart'           => 20,
            'delivery_settings' => json_encode([
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
            'extra_options'     => json_encode([
                'labelAmount'        => 1,
                'digitalStampWeight' => 0,
            ]),
        ],
    ], 'id_delivery_setting');

    MockPsDb::insertRows(AbstractLegacyPsMigration::LEGACY_TABLE_ORDER_LABEL, $orderLabels, 'id_order_label');

    $psOrderShipments = $psOrderShipmentRepository
        ->where('orderId', 1)
        ->values();

    expect($psOrderShipments->count())->toBe(0);

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration $migration */
    $migration = Pdk::get(PdkOrderShipmentsMigration::class);
    $migration->up();
    $migration->up(); // done twice to test that it doesn't migrate shipments with the same id twice

    $pdkOrder         = $pdkOrderRepository->get(1);
    $psOrderShipments = $psOrderShipmentRepository
        ->where('orderId', 1)
        ->values();

    $matchingOrderLabels = array_values(
        Arr::where($orderLabels, function (array $item) {
            return 1 === $item['id_order'];
        })
    );

    foreach ($matchingOrderLabels as $index => $orderLabel) {
        $orderShipment = $psOrderShipments->offsetGet($index);

        expect($orderShipment->getOrderId())
            ->toBe(1)
            ->and($orderShipment->getShipmentId())
            ->toBe($orderLabel['id_label']);
    }

    expect($psOrderShipments->count())->toBe(count($matchingOrderLabels));

    assertMatchesJsonSnapshot(
        json_encode(
            $pdkOrder->shipments
                ->values()
                ->map(function (Shipment $shipment) {
                    // Set dates to a fixed value to prevent snapshot mismatches
                    $shipment->updated = new DateTime('2099-01-01 00:00:00');

                    return $shipment;
                })
                ->toStorableArray()
        )
    );
})->with([
    'single shipment' => [
        'input' => [
            [
                'id_order'        => 1,
                'id_order_label'  => 1,
                'status'          => 'pending - concept',
                'id_label'        => 10000,
                'new_order_state' => '',
                'barcode'         => '3SMYPA1234567',
                'track_link'      => 'https://myparcel.me/track-trace/3SMYPA1234567/2132JE/NL',
                'payment_url'     => '',
                'is_return'       => 0,
                'date_add'        => '2023-04-04 16:15:52',
                'date_upd'        => '2023-04-04 16:15:52',
            ],
        ],
    ],

    'multiple shipments' => [
        'input' => [
            [
                'id_order'        => 1,
                'id_order_label'  => 1,
                'status'          => 'pending - registered',
                'id_label'        => 12004,
                'new_order_state' => 1,
                'barcode'         => '3SMYPA1234567',
                'track_link'      => 'https://myparcel.me/track-trace/3SMYPA1234567/2132JE/NL',
                'payment_url'     => '',
                'is_return'       => 0,
                'date_add'        => '2023-04-06 14:31:18',
                'date_upd'        => '2023-04-06 14:31:25',
            ],
            [
                // belongs to nonexistent order, should be skipped
                'id_order'        => 124992,
                'id_order_label'  => 2,
                'status'          => 'pending - registered',
                'id_label'        => 12005,
                'new_order_state' => 1,
                'barcode'         => '3SMYPA1234567',
                'track_link'      => 'https://myparcel.me/track-trace/3SMYPA1234567/2132JE/NL',
                'payment_url'     => '',
                'is_return'       => 0,
                'date_add'        => '2023-04-06 14:31:18',
                'date_upd'        => '2023-04-06 14:31:25',
            ],
            [
                'id_order'        => 1,
                'id_order_label'  => 3,
                'status'          => 'pending - registered',
                'id_label'        => 12006,
                'new_order_state' => 1,
                'barcode'         => '3SMYPA1234567',
                'track_link'      => 'https://myparcel.me/track-trace/3SMYPA1234567/2132JE/NL',
                'payment_url'     => '',
                'is_return'       => 0,
                'date_add'        => '2023-04-06 14:31:18',
                'date_upd'        => '2023-04-06 14:31:25',
            ],
        ],
    ],
]);
