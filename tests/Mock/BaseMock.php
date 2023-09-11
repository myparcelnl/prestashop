<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use BadMethodCallException;
use MyParcelNL\Sdk\src\Support\Str;

abstract class BaseMock
{
    /**
     * @var array
     */
    protected $attributes;

    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            $attribute = Str::snake(substr($name, 3));

            return $this->attributes[$attribute] ?? null;
        }

        throw new BadMethodCallException("Method {$name} does not exist");
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

    /**
     * @param  array $attributes
     *
     * @return void
     */
    protected function fill(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}
