<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Gett\MyparcelBE\Database\Table;

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
