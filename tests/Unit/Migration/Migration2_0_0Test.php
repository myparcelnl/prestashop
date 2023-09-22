<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockMigration;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('runs database and pdk migrations on upgrade to 2.0.0', function () {
    $reset = mockPdkProperties([
        'databaseMigrationClasses' => [MockMigration::class],
        'pdkMigrationClasses'      => [MockMigration::class],
    ]);

    $migration = Pdk::get(Migration2_0_0::class);
    $migration->up();

    /** @var \MyParcelNL\PrestaShop\Tests\Bootstrap\MockMigration $mockMigration */
    $mockMigration = Pdk::get(MockMigration::class);

    expect($mockMigration->getUpCalls())
        ->toBe(2)
        ->and($mockMigration->getDownCalls())
        ->toBe(0);

    $reset();
});
