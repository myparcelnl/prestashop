<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Account\Repository\PsPdkAccountRepository;
use MyParcelNL\PrestaShop\Pdk\Cart\Repository\PsPdkCartRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderNoteRepository;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function DI\value;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psVersionFactory;

usesShared(new UsesMockPsPdkInstance([
    // Must have an initial value to use mockPdkProperties
    'someProperty' => value(null),
]));

it('gets class based on prestashop version number', function (string $version, string $expectedClass) {
    mockPdkProperties([
        'ps.version' => value($version),

        'someProperty' => psVersionFactory([
            ['class' => PsPdkAccountRepository::class, 'version' => 8],
            ['class' => PsPdkCartRepository::class, 'version' => '1.7', 'operator' => '>=',],
            ['class' => PsPdkSettingsRepository::class, 'version' => '1.6', 'operator' => '<'],
            ['class' => PsPdkOrderNoteRepository::class],
        ]),
    ]);

    $result = Pdk::get('someProperty');

    expect($result)->toBeInstanceOf($expectedClass);
})->with([
    'version 9.999.999' => ['9.999.999', PsPdkAccountRepository::class],
    'version 8.2.0'     => ['8.2.0', PsPdkAccountRepository::class],
    'version 1.7.0'     => ['1.7.0', PsPdkCartRepository::class],
    'version 1.6.0'     => ['1.6.0', PsPdkOrderNoteRepository::class],
    'version 1.5.999'   => ['1.5.999', PsPdkSettingsRepository::class],
    'version 1.0.0'     => ['1.0.0', PsPdkSettingsRepository::class],
]);
