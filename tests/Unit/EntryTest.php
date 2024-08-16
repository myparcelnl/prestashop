<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPlugin;
use Throwable;
use function expect;
use function it;
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

it('enables and disables module', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');

    expect($module->enable())
        ->not->toThrow(Throwable::class)
        ->and($module->disable())
        ->not->toThrow(Throwable::class);
});

it('calls getContent method', function () {
    /** @var \MyParcelNL $module */
    $module = Pdk::get('moduleInstance');

    expect($module->getContent())->toEqual('');
});

it('writes upgrade file when prestashop checks if upgrade is needed', function (
    string $version,
    string $expectedFilename,
    string $expectedVersion
) {
    MockMyParcelNL::setVersion($version);

    /** @var \MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL $module */
    $module  = Pdk::get('moduleInstance');
    $appInfo = Pdk::getAppInfo();

    $result = $module::needUpgrade($module);

    $filename = sprintf('%s/upgrade/%s', $appInfo->path, $expectedFilename);

    expect($result)
        ->toBeTrue()
        ->and(file_exists($filename))
        ->toBeTrue()
        ->and(file_get_contents($filename))
        ->toContain("upgrade_module_$expectedVersion");

    unlink($filename);
})->with([
    ['1.0.0', 'upgrade-1.0.0.php', '1_0_0'],
    ['2.5.6', 'upgrade-2.5.6.php', '2_5_6'],
    ['4.0.0-beta.4', 'upgrade-4.0.0-beta.4.php', '4_0_0_beta_4'],
]);
