<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

/** @see \DbQueryCore */
abstract class MockPsDbQuery extends BaseMock
{
    /**
     * @var array
     */
    private $query = [];

    public function __call($name, $arguments)
    {
        return $this->add(__METHOD__, func_get_args());
    }

    public function build(): string
    {
        return json_encode($this->query);
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
