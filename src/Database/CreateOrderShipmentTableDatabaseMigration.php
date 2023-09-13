<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment
 */
final class CreateOrderShipmentTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $table = $this->getTable();
        $this->execute("DROP TABLE IF EXISTS `$table`");
    }

    public function up(): void
    {
        $table = $this->getTable();
        $sql   = <<<SQL
            CREATE TABLE IF NOT EXISTS `$table` (
                `id`          INT AUTO_INCREMENT                                             NOT NULL,
                `id_order`    INT                                                            NOT NULL,
                `id_shipment` INT                                                            NOT NULL,
                `data`        TEXT                                                           NOT NULL,
                `created`     DATETIME DEFAULT CURRENT_TIMESTAMP                             NOT NULL,
                `updated`     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                INDEX UNIQ_AF009CB98D9F6D38 (`id_order`),
                UNIQUE INDEX UNIQ_A85FA67C5210CC49 (`id_shipment`),
                PRIMARY KEY (`id`)
            ) ENGINE={ENGINE} DEFAULT CHARSET=utf8;
SQL;

        $this->execute($sql);
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        return Table::withPrefix(MyparcelnlOrderShipment::getTable());
    }
}
