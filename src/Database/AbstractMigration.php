<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\Pdk\Facade\DefaultLogger;

abstract class AbstractMigration
{
    /**
     * @return bool
     */
    abstract public function down(): bool;

    /**
     * @return bool
     */
    abstract public function up(): bool;

    /**
     * @param  string $sql
     *
     * @return bool
     */
    protected function execute(string $sql): bool
    {
        $replacedSql = strtr($sql, [
            '{ENGINE}' => _MYSQL_ENGINE_,
        ]);

        $trimmedSql = str_replace("\n", ' ', $replacedSql);
        $trimmedSql = trim(preg_replace('/\s+/m', ' ', $trimmedSql));

        try {
            $status = \Db::getInstance(_PS_USE_SQL_SLAVE_)
                ->execute($trimmedSql);
            DefaultLogger::info('Query executed', ['class' => static::class, 'sql' => $trimmedSql]);

            return $status;
        } catch (\Throwable $e) {
            DefaultLogger::error(
                'Query failed',
                [
                    'class' => static::class,
                    'sql'   => $trimmedSql,
                    'error' => $e->getMessage(),
                ]
            );
            return false;
        }
    }
}
