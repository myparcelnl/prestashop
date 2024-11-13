<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 */
final class CreateOrderDataTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $builder) {
            $builder->id('order_id');
            $builder->column('data');
            $builder->column('notes', 'TEXT', true);
            $builder->timestamps();
            $builder->primary(['order_id']);
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlOrderData::getTable();
    }
}
