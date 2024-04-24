<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

function runUpgrade(string $newVersion): void
{
    require_once sprintf('%s/../../upgrade/upgrade-%s.php', __DIR__, $newVersion);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    /**
     * @TODO: App info is currently set hard coded in the pdk and can't be overridden.
     */
    $pdk->set('appInfo', new AppInfo(['version' => $newVersion]));

    $moduleInstance = new MockMyParcelNL($newVersion);

    MockPsModule::setInstance($moduleInstance->name, $moduleInstance);

    $replacedVersion = str_replace('.', '_', $newVersion);
    $result          = call_user_func("upgrade_module_$replacedVersion", $moduleInstance);

    expect($settingsRepository->get(Pdk::get('settingKeyInstalledVersion')))
        ->toBe($newVersion)
        ->and($result)
        ->toBeTrue();
}

it('runs upgrade with previous version saved', function (string $previousVersion, string $newVersion) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $previousVersion);

    runUpgrade($newVersion);
})->with([
    ['3.0.0', '4.0.0'],
]);

it('runs upgrade with old api key saved', function (string $previousVersion, string $newVersion) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsRepository->store('MYPARCELNL_API_KEY', $previousVersion);

    runUpgrade($newVersion);
})->with([
    ['3.0.0', '4.0.0'],
]);
