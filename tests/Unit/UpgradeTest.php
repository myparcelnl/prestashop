<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

function runUpgradeSuccessfully(string $newVersion): void
{
    /** @var \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

    $result = runUpgrade($newVersion);

    expect($settingsRepository->get(Pdk::get('settingKeyInstalledVersion')))
        ->toBe($newVersion)
        ->and($result)
        ->toBeTrue();
}

function runUpgrade(string $newVersion): bool
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    /**
     * @TODO: App info is currently set hard coded in the pdk and is not overridden during initialisation.
     */
    $pdk->set('appInfo', new AppInfo(['version' => $newVersion]));

    $moduleInstance = new MockMyParcelNL($newVersion);

    MockPsModule::setInstance($moduleInstance->name, $moduleInstance);

    return MyParcelModule::install($moduleInstance);
}

it('runs upgrade with previous version saved', function (string $previousVersion, string $newVersion) {
    /** @var \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $previousVersion);

    runUpgradeSuccessfully($newVersion);
})->with([
    ['3.0.0', '4.0.0'],
]);

it('runs upgrade with (invalid) old api key saved', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    /** @var \MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface $configuration */
    $configuration = Pdk::get(PsConfigurationServiceInterface::class);

    $configuration->set('MYPARCELNL_API_KEY', 'invalid-api-key');

    runUpgradeSuccessfully('4.0.0');

    $firstWarningLog = Arr::first($logger->getLogs(), function (array $log) {
        return $log['level'] === LogLevel::WARNING;
    });

    expect($firstWarningLog)->toHaveKeysAndValues([
        'message' => '[PDK]: Existing API key is invalid',
    ]);
});
