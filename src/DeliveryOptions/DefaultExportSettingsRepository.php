<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Sdk\src\Support\Collection;

class DefaultExportSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var string
     */
    protected static $table = Table::TABLE_CARRIER_CONFIGURATION;

    /**
     * @param  int|string $psCarrierId
     *
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    public function getByCarrier($psCarrierId): Collection
    {
        return $this->get()
            ->filter(static function (array $item) use ($psCarrierId): bool {
                return $item['id_carrier'] === (string) $psCarrierId;
            });
    }
}
