<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPrestaShopLogger extends BaseMock
{
    public const LOG_SEVERITY_LEVEL_INFORMATIVE = 1;
    public const LOG_SEVERITY_LEVEL_WARNING     = 2;
    public const LOG_SEVERITY_LEVEL_ERROR       = 3;
    public const LOG_SEVERITY_LEVEL_MAJOR       = 4;
}
