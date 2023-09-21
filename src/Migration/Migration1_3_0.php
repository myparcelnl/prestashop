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

        $query = "alter table `$table` add column `extra_options` text;";

        $this->db->execute($query);
    }
}
