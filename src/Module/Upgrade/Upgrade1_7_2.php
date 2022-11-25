<?php

namespace Gett\MyparcelBE\Module\Upgrade;

use Gett\MyparcelBE\Database\Table;

class Upgrade1_7_2 extends AbstractUpgrade
{
    /**
     * @inheritDoc
     */
    public function upgrade(): void
    {
        $orderLabelTable = Table::withPrefix(Table::TABLE_ORDER_LABEL);

        try {
            $query = <<<SQL
ALTER TABLE $orderLabelTable ADD COLUMN is_return tinyint;
SQL;
            $this->db->execute($query);
        } catch (\Throwable $e) {
            // ignore error when column already exists
        }
    }
}
