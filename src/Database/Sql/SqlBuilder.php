<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

use MyParcelNL\PrestaShop\Database\Sql\Contract\SqlBuilderInterface;

abstract class SqlBuilder implements SqlBuilderInterface
{
    /**
     * @var string
     */
    private $table;

    /**
     * @param  string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @return mixed
     */
    protected function getEngine()
    {
        return _MYSQL_ENGINE_;
    }

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return _DB_PREFIX_ . $this->table;
    }
}
