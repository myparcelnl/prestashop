<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Concern;

use Supplier;
use SupplierFactory;

/**
 * @method $this withIdSupplier(int $idSupplier)
 * @method $this withSupplier(int|Supplier|SupplierFactory $supplier, array $attributes = [])
 */
interface WithSupplier { }
