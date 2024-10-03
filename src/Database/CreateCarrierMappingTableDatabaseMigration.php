<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;

/**
 * @see MyparcelnlCarrierMapping
 */
final class CreateCarrierMappingTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $builder) {
            $builder->id('carrier_id');
            $builder->column('myparcel_carrier', 'VARCHAR(32)');
            $builder->timestamps();
            $builder->primary(['carrier_id']);
            $builder->unique(['myparcel_carrier']);
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlCarrierMapping::getTable();
    }
}
