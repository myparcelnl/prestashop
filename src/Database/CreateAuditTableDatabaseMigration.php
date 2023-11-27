<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlAudit;
use Throwable;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlAudit
 */
final class CreateAuditTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->column('id', 'VARCHAR(36) NOT NULL')
            ->column('data')
            ->column('model', 'VARCHAR(255) NOT NULL')
            ->column('model_identifier', 'VARCHAR(255) NOT NULL')
            ->primary(['id'])
            ->createdTimestamps();

        $this->execute($sql);

        try {
            $indexSql = (new CreateIndexSqlBuilder($this->getTable()))
                ->index('model_identifier_idx', ['model', 'model_identifier']);

            $this->execute($indexSql);
        } catch (Throwable $e) {
            Logger::error('Could not create index.', ['error' => $e->getMessage(), 'class' => self::class]);
        }
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return MyparcelnlAudit::getTable();
    }
}
