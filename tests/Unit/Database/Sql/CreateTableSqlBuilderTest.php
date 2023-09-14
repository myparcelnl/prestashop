<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

use MyParcelNL\PrestaShop\Database\Sql\CreateTableSqlBuilder;

it('builds sql', function () {
    $builder = new CreateTableSqlBuilder('table_name');

    $builder->id('id');
    $builder->column('name', 'VARCHAR(255)');
    $builder->column('nullable', 'VARCHAR(255)', true);
    $builder->primary(['id']);

    $expected = <<<SQL
CREATE TABLE if NOT EXISTS `ps_table_name` (`id` INT(10) unsigned NOT NULL, `name` VARCHAR(255) NOT NULL, `nullable` VARCHAR(255) NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;

    expect($builder->build())->toBe($expected);
});
