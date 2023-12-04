<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Product;

final class PsProductService extends PsSpecificObjectModelService
{
    protected function getClass(): string
    {
        return Product::class;
    }
}
