<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class MockPsFileLogger extends BaseMock
{
    public const DEBUG   = 0;
    public const INFO    = 1;
    public const WARNING = 2;
    public const ERROR   = 3;

    /**
     * @param  int $level
     */
    public function __construct(int $level)
    {
        $this->attributes['level'] = $level;
    }
}
