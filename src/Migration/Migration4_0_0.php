<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Migration\Pdk\AbstractPsPdkMigration;
use Throwable;

final class Migration4_0_0 extends AbstractPsPdkMigration
{
    public function up(): void
    {
        $this->runDatabaseMigrations();
        $this->runPdkMigrations();
        $this->updateAccount();

        EntityManager::flush();
    }

    private function runDatabaseMigrations(): void
    {
        foreach (Pdk::get('databaseMigrationClasses') as $migration) {
            /** @var \MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration $class */
            $class = Pdk::get($migration);
            $class->up();
        }
    }

    private function runPdkMigrations(): void
    {
        foreach (Pdk::get('pdkMigrationClasses') as $migration) {
            /** @var \MyParcelNL\PrestaShop\Migration\Pdk\AbstractPsPdkMigration $instance */
            $instance = Pdk::get($migration);
            $instance->up();
        }
    }

    /**
     * @return void
     */
    private function updateAccount(): void
    {
        try {
            /**
             * When migrating to the pdk, trigger the update account action to get the correct account settings.
             */
            Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);
        } catch (Throwable $e) {
            Logger::warning('Existing API key is invalid', ['exception' => $e]);
        }
    }
}
