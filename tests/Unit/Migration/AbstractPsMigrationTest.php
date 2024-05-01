<?php
/** @noinspection PhpUnused,PhpIllegalPsrClassPathInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

const MOCK_MIGRATION_TABLE = 'test-table';

const INPUT_ROWS = [
    ['name' => 'KEY_TO_DELETE', 'value' => 1],
    ['name' => 'SOMETHING_NICE', 'value' => 'appelboom'],
];

class BaseMockMigration extends AbstractPsMigration
{
    public $downs = 0;

    public $ups   = 0;

    public function __construct()
    {
        parent::__construct();
        $this->reset();
    }

    public function down(): void
    {
        $this->downs++;
    }

    public function getVersion(): string
    {
        return '1000.0.0';
    }

    public function reset(): void
    {
        $this->ups   = 0;
        $this->downs = 0;
    }

    public function up(): void
    {
        $this->ups++;
    }
}

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    /** @var BaseMockMigration $migration */
    $migration = Pdk::get(BaseMockMigration::class);
    $migration->reset();

    MockPsDb::insertRows('test-table', INPUT_ROWS);
});

it('migrates up', function () {
    /** @var BaseMockMigration $migration */
    $migration = Pdk::get(BaseMockMigration::class);

    $migration->up();

    expect($migration->ups)
        ->toBe(1)
        ->and($migration->downs)
        ->toBe(0);
});

it('migrates down', function () {
    /** @var BaseMockMigration $migration */
    $migration = Pdk::get(BaseMockMigration::class);

    $migration->down();

    expect($migration->ups)
        ->toBe(0)
        ->and($migration->downs)
        ->toBe(1);
});

it('executes getAllRows', function () {
    final class GetAllRowsMockMigration extends BaseMockMigration
    {
        public static $result;

        public function up(): void
        {
            self::$result = $this
                ->getAllRows(MOCK_MIGRATION_TABLE)
                ->toArray();
        }
    }

    /** @var GetAllRowsMockMigration $migration */
    $migration = Pdk::get(GetAllRowsMockMigration::class);
    $migration->up();

    expect(GetAllRowsMockMigration::$result)->toBe(INPUT_ROWS);
});

/** TODO */
it('executes getDbValue')->skip();

/** TODO */
it('executes getRows')->skip();

/** TODO */
it('executes insertRows')->skip();

/** TODO */
it('executes deleteWhere')->skip();

it('handles query that returns false', function () {
    final class FalseQueryMockMigration extends BaseMockMigration
    {
        public static $result;

        public function up(): void
        {
            self::$result = $this
                // makes the execution return false
                ->getRows('false')
                ->toArray();
        }
    }

    /** @var FalseQueryMockMigration $migration */
    $migration = Pdk::get(FalseQueryMockMigration::class);
    $migration->up();

    expect(FalseQueryMockMigration::$result)->toBe([]);
});

it('handles query that throws exception', function () {
    final class ExceptionQueryMockMigration extends BaseMockMigration
    {
        public static $result;

        public function up(): void
        {
            self::$result = $this
                // makes the execution return false
                ->getRows('')
                ->toArray();
        }
    }

    /** @var ExceptionQueryMockMigration $migration */
    $migration = Pdk::get(ExceptionQueryMockMigration::class);
    $migration->up();

    expect(ExceptionQueryMockMigration::$result)->toBe([]);
});
