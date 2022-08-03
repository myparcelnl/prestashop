<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

class CreateCarrierConfigurationTableMigration extends AbstractMigration
{
    public function down(): bool
    {
        $table = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        return $this->execute("DROP TABLE IF EXISTS `$table`");
    }

    public function up(): bool
    {
        $table = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $sql   = <<<SQL
            CREATE TABLE IF NOT EXISTS `$table` (
                `id`               INT AUTO_INCREMENT                                             NOT NULL,
                `id_carrier`       INT                                                            NOT NULL,
                `id_configuration` INT                                                            NOT NULL,
                `created`          DATETIME DEFAULT CURRENT_TIMESTAMP                             NOT NULL,
                `updated`          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                UNIQUE INDEX UNIQ_B515571199A4586C (`id_carrier`), 
                UNIQUE INDEX UNIQ_B51557111BCA74B2 (`id_configuration`),
                PRIMARY KEY (`id`)
            ) ENGINE={ENGINE} DEFAULT CHARSET=utf8;
SQL;

        return $this->execute($sql);
    }
}
