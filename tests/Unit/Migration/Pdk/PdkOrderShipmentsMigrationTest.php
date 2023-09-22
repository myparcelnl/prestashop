<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\Facade\Pdk;

it('migrates order shipments to pdk', function () {
    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration $migration */
    $migration = Pdk::get(PdkOrderShipmentsMigration::class);
    $migration->up();
})->skip();
