<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\Support\Str;

class BaseMock implements Arrayable
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

            return $this->getAttribute($attribute);
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
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name): bool
    {
        return null !== $this->getAttribute($name);
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param  string $key
     * @param  null   $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        $attributes = $flags & Arrayable::SKIP_NULL ? Utils::filterNull($this->attributes) : $this->attributes;

        return array_map(static function ($value) use ($flags) {
            return $value instanceof Arrayable
                ? $value->toArray($flags)
                : $value;
        }, $attributes);
    }

    /**
     * @param  array $attributes
     *
     * @return void
     */
    protected function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }
}
