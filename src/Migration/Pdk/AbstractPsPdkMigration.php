<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;

abstract class AbstractPsPdkMigration extends AbstractLegacyPsMigration
{
    public function down(): void
    {
        // do nothing
    }

    public function getVersion(): string
    {
        return '2.0.0';
    }
}
