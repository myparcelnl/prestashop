<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use DateTime;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
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
    psFactory(Order::class)->store();

    MockPsDb::insertRows(
        AbstractLegacyPsMigration::LEGACY_TABLE_ORDER_LABEL,
        array_map(function (array $orderLabel) {
            return array_merge(['id_order' => 1], $orderLabel);
        }, $orderLabels),
        'id_order_label'
    );

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration $migration */
    $migration = Pdk::get(PdkOrderShipmentsMigration::class);
    $migration->up();

    /** @var PdkOrderRepositoryInterface $pdkOrderRepository */
    $pdkOrderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    /** @var PsOrderShipmentRepository $psOrderShipmentRepository */
    $psOrderShipmentRepository = Pdk::get(PsOrderShipmentRepository::class);

    $pdkOrder = $pdkOrderRepository->get(1);

    $allPsOrderShipments  = $psOrderShipmentRepository->all();
    $firstPsOrderShipment = $allPsOrderShipments->first();

    expect($firstPsOrderShipment->getShipmentId())
        ->toBe($orderLabels[0]['id_label'])
        ->and($firstPsOrderShipment->getOrderId())
        ->toBe(1);

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
                'barcode'         => '',
                'track_link'      => '',
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
                'id_label'        => 12000,
                'new_order_state' => 1,
                'barcode'         => '3SMYPA1234567',
                'track_link'      => 'https://myparcel.me/track-trace/3SMYPA1234567/2132JE/NL',
                'payment_url'     => '',
                'is_return'       => 0,
                'date_add'        => '2023-04-06 14:31:18',
                'date_upd'        => '2023-04-06 14:31:25',
            ],
            [
                // does not belong to order 1
                'id_order'        => 2,
                'id_order_label'  => 2,
                'status'          => 'pending - registered',
                'id_label'        => 12001,
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
                'id_label'        => 12002,
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
