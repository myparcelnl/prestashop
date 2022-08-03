<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Sdk\src\Support\Collection;

/**
 * @deprecated
 */
class DefaultExportSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @param  int|string $psCarrierId
     *
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    public function getByCarrier($psCarrierId): Collection
    {
        return $this->get()
            ->filter(static function ($item) use ($psCarrierId): bool {
                if (is_array($item)) {
                    return $item['id_carrier'] === (string) $psCarrierId;
                }
                return false;
            });
    }

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return Table::TABLE_CARRIER_CONFIGURATION;
    }
}
