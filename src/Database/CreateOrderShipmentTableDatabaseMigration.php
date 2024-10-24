<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment
 */
final class CreateOrderShipmentTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $sqlBuilder) {
            $sqlBuilder->id('order_id');
            $sqlBuilder->id('shipment_id');
            $sqlBuilder->column('data', 'TEXT NOT NULL');
            $sqlBuilder->timestamps();
            $sqlBuilder->primary(['shipment_id']);
        });

        $this->createIndex($this->getTable(), function (CreateIndexSqlBuilder $sqlBuilder) {
            $sqlBuilder->index('order_id', ['order_id']);
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlOrderShipment::getTable();
    }
}
