<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory\Concern;

use Currency;
use CurrencyFactory;

/**
 * @method $this withIdCurrency(int $idCurrency)
 * @method $this withCurrency(int|Currency|CurrencyFactory $currency, array $attributes = [])
 */
interface WithCurrency { }
