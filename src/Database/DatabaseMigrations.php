<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;

final class DatabaseMigrations
{
    /**
     * @return class-string<MigrationInterface>[]
     */
    public function get(): array
    {
        return [
            CreateCarrierMappingTableDatabaseMigration::class,
            CreateCartDeliveryOptionsTableDatabaseMigration::class,
            CreateOrderDataTableDatabaseMigration::class,
            CreateOrderShipmentTableDatabaseMigration::class,
            CreateProductSettingsTableDatabaseMigration::class,
        ];
    }
}
