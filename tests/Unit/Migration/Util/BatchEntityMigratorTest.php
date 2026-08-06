<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use RuntimeException;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

const CURSOR_NAME = 'batch_entity_migrator_test';

function givenRows(int ...$productIds): void
{
    $factories = [];

    foreach ($productIds as $productId) {
        $factories[] = factory(MyparcelnlProductSettings::class)
            ->withProductId($productId)
            ->withData(json_encode(['settings' => []]));
    }

    (new FactoryCollection($factories))->store();
}

function migrator(): BatchEntityMigrator
{
    return Pdk::get(BatchEntityMigrator::class);
}

function rows(): PsProductSettingsRepository
{
    return Pdk::get(PsProductSettingsRepository::class);
}

/**
 * Runs a pass, returning both the row count and the ids the callback saw.
 *
 * @return array{0: int, 1: array}
 */
function visit(callable $callback = null, int $batchSize = 500): array
{
    $seen = [];

    $visited = migrator()->each(
        rows(),
        'productId',
        CURSOR_NAME,
        function ($entity) use (&$seen, $callback): void {
            $seen[] = $entity->getProductId();

            if ($callback) {
                $callback($entity);
            }
        },
        $batchSize
    );

    return [$visited, $seen];
}

it('hands every row to the callback', function () {
    givenRows(1, 2, 3);

    [$visited, $seen] = visit();

    expect($visited)->toBe(3)
        ->and($seen)->toBe([1, 2, 3]);
});

it('keeps paging past the batch size', function () {
    // Five rows in batches of two: the loop has to come back for the leftovers rather than stop at the
    // first short page.
    givenRows(1, 2, 3, 4, 5);

    [$visited, $seen] = visit(null, 2);

    expect($visited)->toBe(5)
        ->and($seen)->toBe([1, 2, 3, 4, 5]);
});

it('resumes where the last run stopped instead of starting over', function () {
    // This is what makes the migration survivable: PrestaShop has no scheduler, so a run killed by a
    // timeout has to pick up from its cursor.
    givenRows(1, 2, 3);

    visit();

    [$visited, $seen] = visit();

    expect($visited)->toBe(0)
        ->and($seen)->toBe([]);
});

it('starts over once the cursor is cleared', function () {
    givenRows(1, 2, 3);

    visit();
    migrator()->clearCursor(CURSOR_NAME);

    [$visited] = visit();

    expect($visited)->toBe(3);
});

it('stores the cursor only after a batch is committed', function () {
    givenRows(1, 2, 3);

    visit();

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $cursorKey          = Pdk::get('createSettingsKey')('batch_entity_migrator_cursor_' . CURSOR_NAME);

    expect((int) $settingsRepository->get($cursorKey))->toBe(3);
});

it('skips a row whose callback throws and keeps going', function () {
    // A poison row must not stall the migration, and the cursor advances past it so a retry does not
    // hit it again.
    //
    // Whether a row that threw halfway is left unwritten cannot be asserted here: MockPsEntityManager
    // no-ops flush() and hands back the very object the callback mutated, so the harness draws no line
    // between changed and committed. BatchEntityMigrator detaches such a row for the real entity
    // manager's benefit.
    givenRows(1, 2, 3);

    [$visited, $seen] = visit(function ($entity): void {
        if (2 === $entity->getProductId()) {
            throw new RuntimeException('Unparseable row');
        }
    });

    expect($visited)->toBe(3)
        ->and($seen)->toBe([1, 2, 3]);
});
