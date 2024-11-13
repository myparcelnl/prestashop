<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings
 */
final class CreateProductSettingsTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $builder) {
            $builder->id('product_id');
            $builder->column('data');
            $builder->primary(['product_id']);
            $builder->timestamps();
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlProductSettings::getTable();
    }
}
