<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

final class CreateIndexSqlBuilder extends SqlBuilder
{
    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var string
     */
    private $index;

    public function build(): string
    {
        return sprintf(
            'CREATE INDEX `%s` ON `%s` (%s);',
            $this->index,
            $this->getTable(),
            implode(', ', $this->columns)
        );
    }

    public function index(string $name, array $columns): self
    {
        $this->index = $name;
        $this->columns = $columns;

        return $this;
    }
}
