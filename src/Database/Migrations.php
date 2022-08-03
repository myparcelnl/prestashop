<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

class Migrations
{
    /**
     * @return class-string<\MyParcelNL\PrestaShop\Database\AbstractMigration>[]
     */
    public function get(): array
    {
        return [
            CreateCarrierConfigurationTableMigration::class,
            CreateCartDeliveryOptionsTableMigration::class,
            CreateOrderDataTableMigration::class,
            CreateOrderShipmentTableMigration::class,
            CreateProductSettingsTableMigration::class,
        ];
    }
}
