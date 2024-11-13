<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Concern;

use Cart;
use CartFactory;

/**
 * @method $this withIdCart(int $idCart)
 * @method $this withCart(int|Cart|CartFactory $cart, array $attributes = [])
 */
interface WithCart { }
