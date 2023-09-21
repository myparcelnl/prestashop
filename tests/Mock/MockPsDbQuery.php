<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Arr;

/** @see \DbQueryCore */
abstract class MockPsDbQuery extends BaseMock
{
    /**
     * @var array
     */
    private $query = [];

    public function __call($name, $arguments)
    {
        return $this->add($name, $arguments);
    }

    public function build(): string
    {
        $select = Arr::first($this->query, static function ($item) {
            return 'select' === $item['type'];
        });

        $table = Arr::first($this->query, static function ($item) {
            return 'from' === $item['type'];
        });

        $wheres = Arr::where($this->query, static function ($item) {
            return 'where' === $item['type'];
        });

        $query = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $select['args'] ?? []),
            implode(', ', $table['args'] ?? [])
        );

        if (! empty($wheres)) {
            $query .= sprintf(' WHERE %s', implode(' AND ', Arr::flatten(Arr::pluck($wheres, 'args'))));
        }

        return $query;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param  string $method
     * @param  array  $args
     *
     * @return void
     */
    private function add(string $method, array $args): self
    {
        $methodName = str_replace(__NAMESPACE__ . '\\', '', $method);

        $this->query[] = [
            'type' => $methodName,
            'args' => $args,
        ];

        return $this;
    }
}
