<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Contract;

use Country;
use CountryFactory;

/**
 * @method $this withIdCountry(int $idCountry)
 * @method $this withCountry(int|Country|CountryFactory $country, array $attributes = [])
 */
interface WithCountry { }
