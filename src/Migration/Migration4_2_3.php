<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration;
use MyParcelNL\PrestaShop\Database\Table;

final class Migration4_2_3 extends AbstractDatabaseMigration
{
    public function getVersion(): string
    {
        return '4.2.3';
    }

    public function down(): void
    {
        // Not needed
    }

    public function up(): void
    {
        // get rid of the audits table
        $this->dropTable(Table::TABLE_AUDITS);
    }
}
