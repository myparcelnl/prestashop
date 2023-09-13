<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use ObjectModel;

/**
 * @template T of ObjectModel
 */
interface PsObjectModelFactoryInterface extends PsFactoryInterface
{
    /**
     * @return T
     */
    public function make(): ObjectModel;

    /**
     * @return T
     */
    public function store(): ObjectModel;
}
