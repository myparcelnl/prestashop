<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

final class Migration1_3_0 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.3.0';
    }

    public function up(): void
    {
        $table = $this->getDeliverySettingsTable();

        $query = "ALTER TABLE `$table` ADD COLUMN `extra_options` text;";

        $this->execute($query);
    }
}
