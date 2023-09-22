<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\PrestaShop\Database\Table;

abstract class AbstractLegacyPsMigration extends AbstractPsMigration
{
    public const LEGACY_TABLE_ORDER_LABEL           = 'myparcelnl_order_label';
    public const LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    public const LEGACY_TABLE_DELIVERY_SETTINGS     = 'myparcelnl_delivery_settings';
    public const LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';

    public function down(): void
    {
        // do nothing
    }

    final protected function getCarrierConfigurationTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_CARRIER_CONFIGURATION);
    }

    final protected function getDeliverySettingsTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_DELIVERY_SETTINGS);
    }

    final protected function getOrderLabelTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_ORDER_LABEL);
    }

    final protected function getProductConfigurationTable(): string
    {
        return Table::withPrefix(self::LEGACY_TABLE_PRODUCT_CONFIGURATION);
    }
}
