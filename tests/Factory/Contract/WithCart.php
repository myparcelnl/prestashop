<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use Cart;
use CartFactory;

/**
 * @method $this withIdCart(int $idCart)
 * @method $this withCart(int|Cart|CartFactory $cart, array $attributes = [])
 */
interface WithCart { }
