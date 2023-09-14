<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database\Sql;

final class CreateTableSqlBuilder extends SqlBuilder
{
    public const NOT_NULL = 'NOT NULL';
    public const NULL     = 'NULL';

    private $columns = [];

    /**
     * @var array
     */
    private $primary = [];

    public function build(): string
    {
        $rows = array_map(static function ($column) {
            return sprintf(
                '`%s` %s %s',
                $column['name'],
                $column['type'],
                implode(' ', $column['options'])
            );
        }, $this->columns);

        $string = implode(', ', $rows);

        if (! empty($this->primary)) {
            $string .= sprintf(', PRIMARY KEY (%s)', implode(', ', $this->primary));
        }

        return sprintf(
            'CREATE TABLE if NOT EXISTS `%s` (%s) ENGINE=%s DEFAULT CHARSET=utf8;',
            $this->getTable(),
            $string,
            $this->getEngine()
        );
    }

    /**
     * @param  string $name
     * @param  string $type
     * @param  bool   $nullable
     *
     * @return $this
     */
    public function column(string $name, string $type = 'TEXT', bool $nullable = false): self
    {
        $this->columns[] = [
            'name'    => $name,
            'type'    => $type,
            'options' => [
                $nullable ? self::NULL : self::NOT_NULL,
            ],
        ];

        return $this;
    }

    /**
     * @param  string $name
     *
     * @return $this
     */
    public function id(string $name): self
    {
        $this->columns[] = [
            'name'    => $name,
            'type'    => 'INT(10) unsigned',
            'options' => [self::NOT_NULL],
        ];

        return $this;
    }

    /**
     * @param  array $keys
     *
     * @return $this
     */
    public function primary(array $keys): self
    {
        $this->primary = $keys;

        return $this;
    }

    public function timestamps(): self
    {
        return $this
            ->column('date_add', 'DATETIME')
            ->column('date_upd', 'DATETIME');
    }
}
