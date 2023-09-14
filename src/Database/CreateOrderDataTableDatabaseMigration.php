<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 */
final class CreateOrderDataTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->id('order_id')
            ->column('data')
            ->timestamps()
            ->primary(['order_id']);

        $this->execute($sql);
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlOrderData::getTable();
    }
}
