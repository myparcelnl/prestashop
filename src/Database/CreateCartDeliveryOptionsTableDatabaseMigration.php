<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions
 */
final class CreateCartDeliveryOptionsTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->id('cart_id')
            ->column('data')
            ->timestamps()
            ->primary(['cart_id']);

        $this->execute($sql);
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlCartDeliveryOptions::getTable();
    }
}
