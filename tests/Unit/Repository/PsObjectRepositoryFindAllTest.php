<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)->withOrderId(1)->withData(json_encode([])),
        factory(MyparcelnlOrderData::class)->withOrderId(2)->withData(json_encode([])),
    ]))->store();
});

it('returns an empty collection from findAll when given no ids', function () {
    /** @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository $repository */
    $repository = Pdk::get(PsOrderDataRepository::class);

    // Laravel's findMany() convention: no ids means an empty result, never "all".
    expect($repository->findAll([])->count())->toBe(0);
});

it('returns only the requested records from findAll', function () {
    /** @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository $repository */
    $repository = Pdk::get(PsOrderDataRepository::class);

    $result = $repository->findAll([1]);

    expect($result->count())
        ->toBe(1)
        ->and($result->first()->getOrderId())
        ->toBe(1);
});

it('matches a repository-specific identifier column in findAll', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withOrderId(1)
            ->withShipmentId(555)
            ->withData(json_encode([])),
    ]))->store();

    /** @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository $repository */
    $repository = Pdk::get(PsOrderShipmentRepository::class);

    // The shipment repo overrides getIdentifierColumn() to its primary key (shipmentId).
    $result = $repository->findAll([555]);

    expect($result->count())
        ->toBe(1)
        ->and($result->first()->getShipmentId())
        ->toBe(555);
});

it('falls back to the default identifier column for repositories that do not override it', function () {
    /** @var \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository $repository */
    $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    // No override, so getIdentifierColumn() returns the default 'id'; nothing stored means empty.
    expect($repository->findAll([999])->count())->toBe(0);
});
