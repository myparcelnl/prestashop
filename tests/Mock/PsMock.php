<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

abstract class PsMock
{
    private static $returnValues = [];

    /**
     * @param  mixed ...$values
     *
     * @return void
     */
    public static function mockReturnValue(...$values): void
    {
        if (! isset(self::$returnValues[static::class])) {
            self::$returnValues[static::class] = [];
        }

        foreach ($values as $value) {
            self::$returnValues[static::class][] = $value;
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return null|mixed
     */
    public function __call($name, $arguments)
    {
        if (isset(self::$returnValues[static::class])) {
            return array_shift(self::$returnValues[static::class]);
        }

        return null;
    }
}
