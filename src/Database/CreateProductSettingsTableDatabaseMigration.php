<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings
 */
final class CreateProductSettingsTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->id('product_id')
            ->column('data')
            ->primary(['product_id'])
            ->timestamps();

        $this->execute($sql);
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlProductSettings::getTable();
    }
}
