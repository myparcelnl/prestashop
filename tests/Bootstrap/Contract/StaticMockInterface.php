<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap\Contract;

interface StaticMockInterface
{
    public static function reset(): void;
}
