<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;

final class CreateCarrierMappingTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->id('carrier_id')
            ->column('myparcel_carrier', 'VARCHAR(32)')
            ->primary(['carrier_id', 'myparcel_carrier']);

        $this->execute($sql);
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlCarrierMapping::getTable();
    }
}
