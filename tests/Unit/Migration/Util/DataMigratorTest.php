<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Psr\Log\LoggerInterface;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

test('gets value', function () {
    /** @var \MyParcelNL\PrestaShop\Migration\Util\DataMigrator $migrator */
    $migrator = Pdk::get(DataMigrator::class);

    $result = $migrator->getValue('other_key', [
        'key'         => 'value',
        'other_key'   => 'other_value',
        'another_key' => 'another_value',
    ]);

    expect($result)->toBe('other_value');
});

test('migrates value', function () {
    /** @var \MyParcelNL\PrestaShop\Migration\Util\DataMigrator $migrator */
    $migrator = Pdk::get(DataMigrator::class);

    $transformationMap = [
        new MigratableValue(
            'key',
            'new_key',
            new TransformValue(function ($value) {
                return "new_$value";
            })
        ),
        new MigratableValue(
            'other_key',
            'some_key',
            new CastValue('int')
        ),
        new MigratableValue(
            'key_not_present',
            'new_key_not_present',
            new CastValue('string')
        ),
    ];

    $result = $migrator->transform([
        'key'         => 'value',
        'other_key'   => '123',
        'another_key' => 'another_value',
    ], $transformationMap);

    expect($result)->toBe([
        'new_key'             => 'new_value',
        'some_key'            => 123,
        'new_key_not_present' => '',
    ]);
});

test('handles invalid input', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    /** @var \MyParcelNL\PrestaShop\Migration\Util\DataMigrator $migrator */
    $migrator = Pdk::get(DataMigrator::class);

    $transformationMap = [];

    /** @noinspection PhpParamsInspection */
    $result = $migrator->transform(null, $transformationMap);

    expect($logger->getLogs())
        ->toHaveCount(1)
        ->and($logger->getLogs()[0]['message'])
        ->toContain('transform expects an array or Collection as input, got NULL instead.')
        ->and($result)
        ->toBe([]);
});
