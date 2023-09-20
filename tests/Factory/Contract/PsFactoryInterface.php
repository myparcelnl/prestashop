<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;

interface PsFactoryInterface extends FactoryInterface
{
    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key);

    /**
     * @param  array $data
     *
     * @return $this
     */
    public function with(array $data): self;
}
