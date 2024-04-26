<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\PrestaShop\Migration\Migration4_0_0;

final class PsMigrationService implements MigrationServiceInterface
{
    /**
     * @return class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>[]
     */
    public function all(): array
    {
        return [
            Migration4_0_0::class,
        ];
    }
}
