<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Migration\Pdk\AbstractPsPdkMigration;

final class Migration2_0_0 extends AbstractPsPdkMigration
{
    public function up(): void
    {
        $this->runDatabaseMigrations();
        $this->runPdkMigrations();

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
}
