<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 */
final class CreateOrderDataTableDatabaseMigration extends AbstractDatabaseMigration
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
                `id`       INT AUTO_INCREMENT                                             NOT NULL,
                `id_order` INT                                                            NOT NULL,
                `data`     TEXT                                                           NOT NULL,
                `created`  DATETIME DEFAULT CURRENT_TIMESTAMP                             NOT NULL,
                `updated`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                UNIQUE INDEX UNIQ_AF009CB91BACD2A8 (`id_order`),
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
        return Table::withPrefix(MyparcelnlOrderData::getTable());
    }
}
