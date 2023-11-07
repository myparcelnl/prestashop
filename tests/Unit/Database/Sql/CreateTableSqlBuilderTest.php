<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;

it('builds sql', function (callable $callback, string $expected) {
    $builder = new CreateTableSqlBuilder('table_name');

    $callback($builder);

    expect($builder->build())->toBe($expected);
})->with([
    '2 varchar columns' => [
        'builder' => function () {
            return function (CreateTableSqlBuilder $builder) {
                $builder->id('id');
                $builder->column('name', 'VARCHAR(255)');
                $builder->column('nullable', 'VARCHAR(255)', true);
            };
        },
        'result'  => function () {
            return <<<SQL
CREATE TABLE IF NOT EXISTS `ps_table_name` (`id` INT(10) unsigned NOT NULL, `name` VARCHAR(255) NOT NULL, `nullable` VARCHAR(255) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        },
    ],

    'primary key' => [
        'builder' => function () {
            return function (CreateTableSqlBuilder $builder) {
                $builder->id('id');
                $builder->column('name', 'VARCHAR(255)');
                $builder->column('deleted', 'TINYINT(1)');
                $builder->primary(['id']);
            };
        },
        'result'  => function () {
            return <<<SQL
CREATE TABLE IF NOT EXISTS `ps_table_name` (`id` INT(10) unsigned NOT NULL, `name` VARCHAR(255) NOT NULL, `deleted` TINYINT(1) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        },
    ],

    'unique index' => [
        'builder' => function () {
            return function (CreateTableSqlBuilder $builder) {
                $builder->id('id');
                $builder->column('name', 'VARCHAR(255)');
                $builder->column('deleted', 'TINYINT(1)');
                $builder->unique(['id', 'name']);
            };
        },
        'result'  => function () {
            return <<<SQL
CREATE TABLE IF NOT EXISTS `ps_table_name` (`id` INT(10) unsigned NOT NULL, `name` VARCHAR(255) NOT NULL, `deleted` TINYINT(1) NOT NULL, UNIQUE KEY (id, name)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        },
    ],
]);
