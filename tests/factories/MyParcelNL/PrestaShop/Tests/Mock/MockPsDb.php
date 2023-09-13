<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Sdk\src\Concerns\HasInstance;

abstract class MockPsDb extends BaseMock
{
    use HasInstance;
}
