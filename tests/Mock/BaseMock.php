<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Sdk\src\Support\Str;

abstract class BaseMock implements Arrayable
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param $name
     * @param $arguments
     *
     * @return array|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        // Handle methods like Carrier::getCarriers(), Zone::getZones(), etc.
        if (Str::startsWith($name, 'get') && substr($name, 3) === static::class . 's') {
            return MockPsObjectModels::getByClass(static::class)
                ->toArray();
        }

        return (new static())->$name(...$arguments);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return null|$this|mixed
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            $attribute = Str::snake(substr($name, 3));

            return $this->attributes[$attribute] ?? null;
        }

        // If no matching attribute is found, return $this to silently ignore the call
        return $this;
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
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
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
