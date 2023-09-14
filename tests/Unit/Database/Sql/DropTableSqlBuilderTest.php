<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

it('builds sql', function () {
    $builder = new DropTableSqlBuilder('table_name');

    $expected = 'DROP TABLE if EXISTS `ps_table_name`';

    expect($builder->build())->toBe($expected);
});
