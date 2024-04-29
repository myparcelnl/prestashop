<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,PhpDocFinalChecksInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use RuntimeException;
use Throwable;

class MockMyParcelNL extends MyParcelNL
{
    public function getLocalPath(): string
    {
        throw new RuntimeException('oops');
    }
}

function createModule(): MyParcelNL
{
    $module = new MockMyParcelNL();

    MockPsModule::setInstance($module->name, $module);

    return $module;
}

it('handles pdk errors: installs and uninstalls module', function () {
    $module = createModule();

    expect($module->install())
        ->not->toThrow(Throwable::class)
        ->and($module->uninstall())
        ->not->toThrow(Throwable::class);
});

it('handles pdk errors: enables and disables module', function () {
    $module = createModule();

    expect($module->enable())
        ->not->toThrow(Throwable::class)
        ->and($module->disable())
        ->not->toThrow(Throwable::class);
});

it('handles pdk errors: calls getContent method', function () {
    $module = createModule();

    expect($module->getContent())->toEqual('');
});

