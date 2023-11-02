<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use Carrier;
use CarrierFactory;

/**
 * @method $this withIdCarrier(int $idCarrier)
 * @method $this withCarrier(int|Carrier|CarrierFactory $carrier, array $attributes = [])
 */
interface WithCarrier { }
