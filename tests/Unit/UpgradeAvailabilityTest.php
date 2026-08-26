<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPendingMigrationsInstaller;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function DI\get;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance([
    InstallerServiceInterface::class => get(MockPendingMigrationsInstaller::class),
]));

beforeEach(function () {
    MockPendingMigrationsInstaller::reset();
});

it('leaves the registered version behind while migrations are pending', function () {
    // canBeUpgraded() compares the registered version with the one on disk, so leaving it behind is
    // what keeps PrestaShop offering the upgrade. Bumping it would hide the button and strand
    // whatever is left unconverted.
    MockPendingMigrationsInstaller::$pending = true;

    $result = MyParcelNL::upgradeModuleVersion(MyParcelNL::MODULE_NAME, '9.9.9');

    expect($result)->toBeFalse()
        ->and(MockPsModule::getRegisteredVersions())->not->toHaveKey(MyParcelNL::MODULE_NAME);
});

it('records the registered version once nothing is pending', function () {
    MockPendingMigrationsInstaller::$pending = false;

    $result = MyParcelNL::upgradeModuleVersion(MyParcelNL::MODULE_NAME, '9.9.9');

    expect($result)->toBeTrue()
        ->and(MockPsModule::getRegisteredVersions())->toHaveKey(MyParcelNL::MODULE_NAME);
});
