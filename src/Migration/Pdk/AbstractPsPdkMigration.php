<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;

abstract class AbstractPsPdkMigration extends AbstractPsMigration
{
    public function down(): void
    {
        // do nothing
    }

    public function getVersion(): string
    {
        return Pdk::get('pdkMigrationVersion');
    }
}
