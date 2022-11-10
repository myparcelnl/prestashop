<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Db;
use MyParcelNL\PrestaShop\Database\Table;

trait CarrierHooks
{
    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionCarrierUpdate(array $params): void
    {
        $oldCarrierId = (int) $params['id_carrier'];
        $newCarrier   = $params['carrier']; // Carrier object
        $table        = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->execute(
                <<<SQL
INSERT INTO `$table` (`id_carrier`, `name`, `value`) 
SELECT $newCarrier->id AS `id_carrier`, `name`, `value` FROM 
`$table`
WHERE `id_carrier` = $oldCarrierId
SQL
            );
    }
}
