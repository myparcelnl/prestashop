<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_1_2;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_3_0;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_4_0;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_6_0;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_7_2;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration1_8_0;
use MyParcelNL\PrestaShop\Pdk\Installer\Migration\Migration2_0_0;

final class PsMigrationService implements MigrationServiceInterface
{
    /**
     * @return class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>[]
     */
    public function all(): array
    {
        return [
            Migration1_1_2::class,
            Migration1_3_0::class,
            Migration1_4_0::class,
            Migration1_6_0::class,
            Migration1_7_2::class,
            Migration1_8_0::class,
            Migration2_0_0::class,
        ];
    }
}
