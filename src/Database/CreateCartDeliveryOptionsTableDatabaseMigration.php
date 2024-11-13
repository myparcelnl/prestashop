<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions
 */
final class CreateCartDeliveryOptionsTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $builder) {
            $builder->id('cart_id');
            $builder->column('data');
            $builder->timestamps();
            $builder->primary(['cart_id']);
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlCartDeliveryOptions::getTable();
    }
}
