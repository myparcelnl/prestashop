<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use Db;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface;
use MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;
use MyParcelNL\PrestaShop\Database\Sql\DropTableSqlBuilder;
use Throwable;

abstract class AbstractDatabaseMigration implements MigrationInterface
{
    public function getVersion(): string
    {
        return '';
    }

    /**
     * @param  string                                                              $table
     * @param  callable<\MyParcelNL\PrestaShop\Database\Sql\CreateIndexSqlBuilder> $callable
     *
     * @return mixed
     */
    protected function createIndex(string $table, callable $callable)
    {
        $builder = $this->wrapBuilder($table, CreateIndexSqlBuilder::class, $callable);

        try {
            return $this->execute($builder);
        } catch (Throwable $e) {
            Logger::warning(
                'Failed to create index.',
                array_replace(['class' => static::class, 'table' => $table], ['error' => $e->getMessage()])
            );

            return false;
        }
    }

    /**
     * @param  string                                                              $table
     * @param  callable<\MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder> $callable
     *
     * @return mixed
     */
    protected function createTable(string $table, callable $callable)
    {
        $builder = $this->wrapBuilder($table, CreateTableSqlBuilder::class, $callable);

        return $this->executeWithErrorHandling($builder);
    }

    /**
     * @param  string $table
     *
     * @return void
     */
    protected function dropTable(string $table): void
    {
        $this->executeWithErrorHandling(new DropTableSqlBuilder($table));
    }

    /**
     * @param  string|\MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface $sql
     *
     * @return mixed
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function execute($sql, bool $getResults = false)
    {
        $resolvedSql = $this->resolveSql($sql);
        $instance    = Db::getInstance(_PS_USE_SQL_SLAVE_);

        if ($getResults) {
            $result = $instance->executeS($resolvedSql);
        } else {
            $result = $instance->execute($resolvedSql);
        }

        Logger::debug('Query executed', ['class' => static::class, 'sql' => $sql]);

        return $result;
    }

    /**
     * @param  string|\MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface $sql
     * @param  bool                                                                    $getResults
     *
     * @return mixed
     */
    protected function executeWithErrorHandling($sql, bool $getResults = false)
    {
        $resolvedSql = $this->resolveSql($sql);

        try {
            return $this->execute($resolvedSql, $getResults);
        } catch (Throwable $e) {
            Logger::error(
                'Failed to execute query.',
                array_replace(['class' => static::class, 'sql' => $resolvedSql], ['error' => $e->getMessage()])
            );
        }

        return false;
    }

    /**
     * @param  string|\MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface $sql
     *
     * @return string
     */
    private function resolveSql($sql): string
    {
        if ($sql instanceof SqlBuilderInterface) {
            $sql = $sql->build();
        }

        return $sql;
    }

    /**
     * @template T of \MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface
     * @param  string          $table
     * @param  class-string<T> $builderClass
     * @param  callable<T>     $callable
     *
     * @return T
     */
    private function wrapBuilder(string $table, string $builderClass, callable $callable): SqlBuilderInterface
    {
        $builder = new $builderClass($table);

        $callable($builder);

        return $builder;
    }
}
