<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPsClass
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes = $data;
    }

    /**
     * @param  string $name
     *
     * @return null|mixed
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
}
