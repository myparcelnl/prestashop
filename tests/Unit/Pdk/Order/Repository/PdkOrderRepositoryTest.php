<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Pdk\Order\Repository;

use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Bootstrap\PsMockPdkConfig;

beforeEach(function () {
    PdkFactory::create(PsMockPdkConfig::create());
});

it('can get an order by id', function (): void {
    /** @var AbstractPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(AbstractPdkOrderRepository::class);

    $order = $this->getOrderRepository()
        ->getById(1);

    expect($order->getId())->toBe(1);
});
