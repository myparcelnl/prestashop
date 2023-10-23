<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPlugin;
use Throwable;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPlugin());

it('boots plugin', function () {
    expect(true)->toBeTrue();
});

it('has hooks', function () {
    assertMatchesJsonSnapshot(json_encode(Pdk::get('moduleHooks')));
});

it('installs and uninstalls module', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');

    expect($module->install())
        ->not->toThrow(Throwable::class)
        ->and($module->uninstall())
        ->not->toThrow(Throwable::class);
});
