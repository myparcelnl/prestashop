<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Facade\Logger;
use Throwable;

final class Migration1_7_2 extends AbstractLegacyPsMigration
{
    public function getVersion(): string
    {
        return '1.7.2';
    }

    public function up(): void
    {
        $table = $this->getOrderLabelTable();

        try {
            $query = "ALTER TABLE `$table` ADD COLUMN `is_return` tinyint;";

            $this->db->execute($query);
        } catch (Throwable $e) {
            Logger::error($e->getMessage());
        }
    }
}
