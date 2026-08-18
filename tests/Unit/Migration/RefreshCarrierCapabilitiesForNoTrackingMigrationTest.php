<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use RuntimeException;

use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

const REFRESH_MIGRATION_ID = '2026_08_04_145556_refresh_carrier_capabilities_for_no_tracking';

/**
 * Loads the migration the same way the installer does: require the file and take the returned
 * anonymous-class instance.
 */
function loadRefreshMigration(): TimestampedMigrationInterface
{
    return require __DIR__ . '/../../../src/Migration/' . REFRESH_MIGRATION_ID . '.php';
}

/**
 * A repository that records whether fresh data was asked for, and can be told to fail.
 */
function capabilitiesSpy(bool $throws = false): CarrierCapabilitiesRepository
{
    return new class(Pdk::get(StorageInterface::class), Pdk::get(CapabilitiesService::class), $throws) extends
        CarrierCapabilitiesRepository {
        /** @var null|bool */
        public $freshRequested;

        /** @var bool */
        private $throws;

        public function __construct($storage, $apiService, bool $throws)
        {
            parent::__construct($storage, $apiService);

            $this->throws = $throws;
        }

        public function getContractDefinitions(?string $carrier = null, bool $fresh = false): CarrierCollection
        {
            $this->freshRequested = $fresh;

            if ($this->throws) {
                throw new RuntimeException('API unavailable');
            }

            return new CarrierCollection();
        }
    };
}

it('is a timestamped migration the installer can discover', function () {
    $migration = loadRefreshMigration();

    $migration->setIdentity(REFRESH_MIGRATION_ID);

    expect($migration)->toBeInstanceOf(TimestampedMigrationInterface::class)
        ->and($migration->getId())->toBe(REFRESH_MIGRATION_ID);
});

it('runs after the migration that inverts the stored option', function () {
    // The refresh has to happen once the feature flag is being sent, and timestamped migrations run in
    // filename order, so this one has to sort later than the inversion.
    expect(REFRESH_MIGRATION_ID > '2026_08_04_144911_invert_tracked_to_no_tracking')->toBeTrue();
});

it('asks for fresh contract definitions rather than the cached copy', function () {
    TestBootstrapper::hasAccount();
    // getAccount(true) forces a refresh, which calls the accounts endpoint.
    MockApi::enqueue(new ExampleGetAccountsResponse());

    $spy = capabilitiesSpy();

    mockPdkProperties([CarrierCapabilitiesRepository::class => $spy]);

    loadRefreshMigration()->up();

    // Passing false would re-store the pre-flag copy, which is the state this migration exists to replace.
    expect($spy->freshRequested)->toBeTrue();
});

it('reports failure without throwing when the account cannot be refreshed', function () {
    // The forced account refresh calls the API, so it can fail on its own before the carrier definitions
    // are ever asked for. That path has to report failure too, rather than abort the upgrade.
    mockPdkProperties([
        PdkAccountRepositoryInterface::class => new class {
            public function getAccount(bool $fresh = false)
            {
                throw new RuntimeException('Accounts endpoint unavailable');
            }
        },
    ]);

    $migration = loadRefreshMigration();

    $migration->up();

    expect($migration->hasFailed())->toBeTrue();
});

it('reports failure without throwing when fetching carrier definitions fails', function () {
    TestBootstrapper::hasAccount();
    // getAccount(true) forces a refresh, which calls the accounts endpoint.
    MockApi::enqueue(new ExampleGetAccountsResponse());

    mockPdkProperties([CarrierCapabilitiesRepository::class => capabilitiesSpy(true)]);

    $migration = loadRefreshMigration();

    // Throwing would leave a shop without a working API key unable to finish upgrading. Reporting failure
    // keeps the migration unrecorded, so it is attempted again rather than blocking anything.
    $migration->up();

    expect($migration->hasFailed())->toBeTrue();
});

it('leaves existing carrier data alone when the fetch fails', function () {
    TestBootstrapper::hasAccount();
    // getAccount(true) forces a refresh, which calls the accounts endpoint.
    MockApi::enqueue(new ExampleGetAccountsResponse());

    /** @var PdkAccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $before            = $accountRepository->getAccount()
        ->shops->first()
        ->carriers->count();

    mockPdkProperties([CarrierCapabilitiesRepository::class => capabilitiesSpy(true)]);

    loadRefreshMigration()->up();

    // Nothing is stored on failure, so a half-written or emptied carrier list is not left behind.
    expect(
        $accountRepository->getAccount()
            ->shops->first()
            ->carriers->count()
    )->toBe($before);
});
