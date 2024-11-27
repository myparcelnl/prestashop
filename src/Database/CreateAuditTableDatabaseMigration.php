<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlAudit;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlAudit
 */
final class CreateAuditTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->dropTable($this->getTable());
    }

    public function up(): void
    {
        $this->createTable($this->getTable(), function (CreateTableSqlBuilder $builder) {
            $builder->id('id');
            $builder->column('action', 'VARCHAR(255) NOT NULL');
            $builder->column('data');
            $builder->column('model', 'VARCHAR(255) NOT NULL');
            $builder->column('model_identifier', 'VARCHAR(255) NOT NULL');
            $builder->timestamps();
            $builder->primary(['id']);
        });

        $this->createIndex($this->getTable(), function (CreateIndexSqlBuilder $builder) {
            $builder->index('model_identifier_idx', ['model', 'model_identifier']);
        });
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlAudit::getTable();
    }
}
