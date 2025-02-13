<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration;
use MyParcelNL\PrestaShop\Entity\MyparcelnlAudit;

final class RemoveAuditTableMigration extends AbstractDatabaseMigration
{
    public function down(): void
    {
        // Not needed because we want to remove the table permanently
    }

    public function up(): void
    {
        $this->dropTable(MyparcelnlAudit::getTable());
    }
}