<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

it('creates sql', function () {
    $builder = new CreateIndexSqlBuilder('table');

    $builder->index('index_123', ['column1', 'column2']);

    $result = <<<SQL
CREATE INDEX `index_123` ON `table` (column1, column2);
SQL;

    expect($builder->build())->toEqual($result);
});
