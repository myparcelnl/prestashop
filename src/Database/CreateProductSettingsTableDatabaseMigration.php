<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings
 */
class CreateProductSettingsTableDatabaseMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        $table = Table::withPrefix(Table::TABLE_PRODUCT_SETTINGS);
        $this->execute("DROP TABLE IF EXISTS `$table`");
    }

    public function up(): void
    {
        $table = Table::withPrefix(Table::TABLE_PRODUCT_SETTINGS);
        $sql   = <<<SQL
            CREATE TABLE IF NOT EXISTS `$table` (
                `id`         INT AUTO_INCREMENT                                             NOT NULL,
                `id_product` INT                                                            NOT NULL,
                `data`       TEXT                                                           NOT NULL,
                `created`    DATETIME DEFAULT CURRENT_TIMESTAMP                             NOT NULL,
                `updated`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                UNIQUE INDEX UNIQ_A2BF85F5DD7ADDD (`id_product`),
                PRIMARY KEY (`id`)
            ) ENGINE={ENGINE} DEFAULT CHARSET=utf8;
SQL;

        $this->execute($sql);
    }
}