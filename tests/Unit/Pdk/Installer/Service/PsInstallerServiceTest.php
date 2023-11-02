<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPlugin;
use MyParcelNL\Sdk\src\Support\Collection;
use Tab;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

const MYPARCEL_TABLES = [
    'ps_myparcelnl_carrier_mapping',
    'ps_myparcelnl_cart_delivery_options',
    'ps_myparcelnl_order_data',
    'ps_myparcelnl_order_shipment',
    'ps_myparcelnl_product_settings',
];

usesShared(new UsesMockPlugin());

it('executes database migrations', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');

    expect(MockPsDb::getDatabase())->not->toHaveKeys(MYPARCEL_TABLES);

    Installer::install($module);

    expect(MockPsDb::getDatabase())->toHaveKeys(MYPARCEL_TABLES);
});

it('installs tabs', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');
    /** @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
    $tabRepository = Pdk::get('ps.tabRepository');

    expect($tabRepository->findAll())->toBeEmpty();
    Installer::install($module);

    $this->assertMatchesJsonSnapshot(
        json_encode((new Collection($tabRepository->findByModule($module->name)))->toArray())
    );
});

it('doesn\'t add same tab twice', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');
    /** @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
    $tabRepository = Pdk::get('ps.tabRepository');

    psFactory(Tab::class)
        ->withModule($module->name)
        ->withClassName(Pdk::get('legacyControllerSettings'))
        ->store();

    expect($tabRepository->findByModule($module->name))->toHaveCount(1);
    Installer::install($module);
    expect($tabRepository->findByModule($module->name))->toHaveCount(1);
});
