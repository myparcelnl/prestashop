<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;

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

        try {
            \Db::getInstance(_PS_USE_SQL_SLAVE_)
                ->execute($trimmedSql);
            Logger::info('Query executed', ['class' => static::class, 'sql' => $trimmedSql]);
        } catch (\Throwable $e) {
            Logger::error(
                'Query failed',
                [
                    'class' => static::class,
                    'sql'   => $trimmedSql,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
