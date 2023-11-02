<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use Customer;
use CustomerFactory;

/**
 * @method $this withIdCustomer(int $idCustomer)
 * @method $this withCustomer(int|Customer|CustomerFactory $customer, array $attributes = [])
 */
interface WithCustomer { }
