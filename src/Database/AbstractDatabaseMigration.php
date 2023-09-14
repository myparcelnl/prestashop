<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use Db;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface;

abstract class AbstractDatabaseMigration implements MigrationInterface
{
    public function getVersion(): string
    {
        return '';
    }

    /**
     * @param  string|\MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface $sql
     *
     * @return void
     */
    protected function execute($sql): void
    {
        if ($sql instanceof SqlBuilderInterface) {
            $sql = $sql->build();
        }

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->execute($sql);

        Logger::debug('Query executed', ['class' => static::class, 'sql' => $sql]);
    }
}
