<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use BadMethodCallException;
use MyParcelNL\Pdk\Tests\Factory\AbstractFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsFactoryInterface;
use MyParcelNL\Sdk\src\Support\Str;

abstract class AbstractPsFactory extends AbstractFactory implements PsFactoryInterface
{
    /**
     * @param  mixed $name
     * @param  mixed $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'with')) {
            $attribute = Str::snake(Str::after($name, 'with'));
            $value     = $arguments[0];

            return $this->addAttribute($attribute, $value, $arguments[1] ?? []);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist', $name));
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes->get($key);
    }

    /**
     * @param  array<string, mixed> $data
     *
     * @return $this
     */
    public function with(array $data): PsFactoryInterface
    {
        $this->attributes = $this->attributes->merge($data);

        return $this;
    }

    /**
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return $this
     */
    protected function addAttribute(string $attribute, $value): self
    {
        return $this->with([$attribute => $value]);
    }
}
