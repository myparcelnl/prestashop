<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

final class DropTableSqlBuilder extends SqlBuilder
{
    public function build(): string
    {
        return sprintf('DROP TABLE if EXISTS `%s`', $this->getTable());
    }
}
