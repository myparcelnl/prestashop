<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Db;
use Gett\MyparcelBE\Database\Table;

trait CarrierHooks
{
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
