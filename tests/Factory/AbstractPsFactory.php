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

            return $this->with([$attribute => $value]);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist', $name));
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
}
