<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 */
class CreateOrderDataTableMigration extends AbstractMigration
{
    public function down(): bool
    {
        $table = Table::withPrefix(Table::TABLE_ORDER_DATA);
        return $this->execute("DROP TABLE IF EXISTS `$table`");
    }

    public function up(): bool
    {
        $table = Table::withPrefix(Table::TABLE_ORDER_DATA);
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

        return $this->execute($sql);
    }
}
