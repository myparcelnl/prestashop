<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Settings\Repository;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\setupCarrierActiveSettings;

usesShared(new UsesMockPsPdkInstance());

it('updates carrier active state when carrier settings are edited', function (array $settings, bool $result) {
    /** @var \MyParcelNL\Pdk\Settings\Model\Settings $settingsModel */
    $settingsModel = setupCarrierActiveSettings($settings)->make();

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
    $repository = Pdk::get(SettingsRepositoryInterface::class);
    $repository->storeAllSettings($settingsModel);

    $psCarrier = new PsCarrier(12);

    /** @noinspection PhpCastIsUnnecessaryInspection */
    expect((bool) $psCarrier->active)->toBe($result);
})->with('carrierActiveSettings');
