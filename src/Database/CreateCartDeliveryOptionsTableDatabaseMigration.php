<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions
 */
final class CreateCartDeliveryOptionsTableDatabaseMigration extends AbstractDatabaseMigration
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
                `id`      INT AUTO_INCREMENT                                             NOT NULL,
                `id_cart` INT                                                            NOT NULL,
                `data`    TEXT                                                           NOT NULL,
                `created` DATETIME DEFAULT CURRENT_TIMESTAMP                             NOT NULL,
                `updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                UNIQUE INDEX UNIQ_8CA4157F808394B5 (`id_cart`),
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
        return Table::withPrefix(MyparcelnlCartDeliveryOptions::getTable());
    }
}
