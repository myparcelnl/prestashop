<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use Throwable;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment
 */
final class CreateOrderShipmentTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $this->execute(new DropTableSqlBuilder($this->getTable()));
    }

    public function up(): void
    {
        $sql = (new CreateTableSqlBuilder($this->getTable()))
            ->id('order_id')
            ->id('shipment_id')
            ->column('data', 'TEXT NOT NULL')
            ->timestamps()
            ->primary(['shipment_id']);

        $this->execute($sql);

        try {
            $indexSql = (new CreateIndexSqlBuilder($this->getTable()))->index('order_id', ['order_id']);

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
        return MyparcelnlOrderShipment::getTable();
    }
}
