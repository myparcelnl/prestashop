<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

final class Migration1_7_2 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.7.2';
    }

    public function up(): void
    {
        $table = $this->getOrderLabelTable();

        $query = "ALTER TABLE `$table` ADD COLUMN `is_return` tinyint;";

        $this->execute($query);
    }
}
