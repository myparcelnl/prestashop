<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Logger;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function DI\get;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPsPdkInstance([
        LoggerInterface::class => get(PsLogger::class),
    ])
);

it('logs to a file', function (string $severity) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    /** @var LoggerInterface $logger */
    $logger = Pdk::get(LoggerInterface::class);

    $logger->{$severity}('test', ['context' => 'test']);

    $path     = Pdk::get('logDirectory');
    $filename = sprintf('%s/%s.log', $path, $severity);

    expect($fileSystem->fileExists($filename))->toBeTrue();
})->with([
    LogLevel::DEBUG,
    LogLevel::INFO,
    LogLevel::NOTICE,
    LogLevel::WARNING,
    LogLevel::ERROR,
    LogLevel::CRITICAL,
    LogLevel::ALERT,
    LogLevel::EMERGENCY,
]);

it('creates all log files on initialize if pdk is in dev mode', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $reset = mockPdkProperty('mode', \MyParcelNL\Pdk\Base\Pdk::MODE_DEVELOPMENT);

    /** @var LoggerInterface $logger */
    $logger = Pdk::get(LoggerInterface::class);

    $logger->debug('something random');

    /** @var array<string, string> $map */
    $map       = Pdk::get('logLevelFilenameMap');
    $directory = Pdk::get('logDirectory');

    expect($map)
        ->toBeArray()
        ->and($map)->not->toBeEmpty();

    foreach ($map as $level => $filename) {
        expect($fileSystem->fileExists("$directory/$level.log"))->toBeTrue();
    }

    $reset();
});
