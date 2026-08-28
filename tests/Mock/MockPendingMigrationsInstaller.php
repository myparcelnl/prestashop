<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;

/**
 * Reports whatever a test wants about pending migrations.
 *
 * The real installer answers this from the applied-migrations list, which needs a full upgrade run to
 * populate. A test that only cares what the module does with the answer sets it directly.
 */
final class MockPendingMigrationsInstaller implements InstallerServiceInterface
{
    /**
     * @var bool
     */
    public static $pending = false;

    public static function reset(): void
    {
        self::$pending = false;
    }

    public function hasPendingMigrations(): bool
    {
        return self::$pending;
    }

    public function install(...$args): void
    {
        // Nothing to install: this stands in for the installer only to answer the pending check.
    }

    public function uninstall(...$args): void
    {
        // Nothing to uninstall, same reason.
    }
}
