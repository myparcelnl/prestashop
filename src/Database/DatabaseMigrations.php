<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

final class DatabaseMigrations
{
    /**
     * @return class-string<\MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration>[]
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
