<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL;

class MockMyParcelNL extends MyParcelNL
{
    /**
     * @var string|null
     */
    private static $version;

    /**
     * @param  null|string $version
     *
     * @throws \Throwable
     */
    public function __construct(?string $version = null)
    {
        self::$version = $version;
        parent::__construct();
    }

    /**
     * @param  null|string $version
     *
     * @return void
     */
    public static function setVersion(?string $version): void
    {
        self::$version = $version;
    }

    protected static function getVersionFromComposer(): string
    {
        return self::$version ?? parent::getVersionFromComposer();
    }
}
