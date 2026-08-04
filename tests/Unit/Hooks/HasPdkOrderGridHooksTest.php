<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Order;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function MyParcelNL\PrestaShop\setupAccountAndCarriers;

final class ClassWithPdkOrderGridHooks
{
    use HasPdkOrderGridHooks;
}

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
    );
});

/**
 * Runs the grid presenter hook for a single order and returns the resulting grid row.
 */
function presentGridRowForOrder(Order $order): array
{
    $params = [
        'presented_grid' => [
            'data' => [
                'records' => new RecordCollection([
                    [
                        'id_order'  => (int) $order->id,
                        'reference' => 'REF-GRID',
                        'date_add'  => '2020-01-01 10:00:00',
                        'payment'   => 'Cash on delivery',
                    ],
                ]),
            ],
        ],
    ];

    (new ClassWithPdkOrderGridHooks())->hookActionOrderGridPresenterModifier($params);

    return $params['presented_grid']['data']['records']->all()[0];
}

it('renders the myparcel widget for an order with a myparcel carrier', function () {
    psFactory(PsCarrier::class)
        ->withId(93)
        ->store();

    psFactory(MyparcelnlCarrierMapping::class)
        ->withCarrierId(93)
        ->withMyparcelCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(93)
        ->store();

    $row = presentGridRowForOrder($order);

    expect($row)->toHaveKey('myparcel');
});

it('does not render the myparcel widget for an order with a non-myparcel carrier', function () {
    // Carrier 94 deliberately has NO row in myparcelnl_carrier_mapping.
    psFactory(PsCarrier::class)
        ->withId(94)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(94)
        ->store();

    $row = presentGridRowForOrder($order);

    expect($row)->not->toHaveKey('myparcel');
});

it('renders the myparcel widget for an order with a non-myparcel carrier that was already exported', function () {
    // The carrier is not mapped, but the order has a shipment: the widget must stay visible so
    // the label status remains accessible (also covers carrier-id drift on old orders).
    psFactory(PsCarrier::class)
        ->withId(94)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(94)
        ->store();

    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withOrderId((int) $order->id)
            ->withShipmentId(987)
            ->withData(json_encode(['id' => 987])),
    ]))->store();

    $row = presentGridRowForOrder($order);

    expect($row)->toHaveKey('myparcel');
});
