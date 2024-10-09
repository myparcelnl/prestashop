<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Concern;

use Warehouse;
use WarehouseFactory;

/**
 * @method $this withIdWarehouse(int $idWarehouse)
 * @method $this withWarehouse(int|Warehouse|WarehouseFactory $warehouse, array $attributes = [])
 */
interface WithWarehouse { }
