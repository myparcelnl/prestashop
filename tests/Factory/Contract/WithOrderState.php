<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use OrderState;
use OrderStateFactory;

/**
 * @method $this withIdOrderState(int $idOrderState)
 * @method $this withOrderState(int|OrderState|OrderStateFactory $orderState, array $attributes = [])
 */
interface WithOrderState { }
