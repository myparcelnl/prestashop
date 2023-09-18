<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;

interface PsFactoryInterface extends FactoryInterface
{
    /**
     * @param  array $data
     *
     * @return $this
     */
    public function with(array $data): self;
}
