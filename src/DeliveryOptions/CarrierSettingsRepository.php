<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\DeliveryOptions;

use MyParcelNL\PrestaShop\Database\Table;

/**
 * @deprecated
 */
class CarrierSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @return string
     */
    protected function getTable(): string
    {
        return Table::TABLE_CARRIER_CONFIGURATION;
    }
}
