<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use Db;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Logger;

abstract class AbstractDatabaseMigration implements MigrationInterface
{
    public function getVersion(): string
    {
        return '';
    }

    /**
     * @param  string $sql
     *
     * @return void
     */
    protected function execute(string $sql): void
    {
        $replacedSql = strtr($sql, [
            '{ENGINE}' => _MYSQL_ENGINE_,
        ]);

        $trimmedSql = str_replace("\n", ' ', $replacedSql);
        $trimmedSql = trim(preg_replace('/\s+/m', ' ', $trimmedSql));

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->execute($trimmedSql);

        Logger::debug('Query executed', ['class' => static::class, 'sql' => $trimmedSql]);
    }
}
