<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Installer;

use MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\PrestaShop\Module\Migration\Migration2_0_0;

final class PsMigrationService implements MigrationServiceInterface
{
    /**
     * TODO: move all migrations to this structure
     *
     * @return string[]
     */
    public function all(): array
    {
        return [
            Migration2_0_0::class,
        ];
    }
}
