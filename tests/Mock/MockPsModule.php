<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use Context;
use DI\Container;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

abstract class MockPsModule extends BaseMock implements StaticMockInterface
{
    /**
     * @var array<string, self>
     */
    protected static $instances = [];

    /**
     * @var \DI\Container
     */
    public $container;

    /**
     * @var \Context
     */
    public $context;

    public function __construct()
    {
        $this->context   = Context::getContext();
        $this->container = $this->context->container;

        $this->attributes['local_path'] = __DIR__ . '/../..';
    }

    /**
     * @param  string $name
     *
     * @return false|self
     * @noinspection ProperNullCoalescingOperatorUsageInspection
     */
    public static function getInstanceByName(string $name)
    {
        return static::$instances[$name] ?? false;
    }

    public static function reset(): void
    {
        static::$instances = [];
    }

    /**
     * @param  string $name
     * @param  self   $instance
     *
     * @return void
     */
    public static function setInstance(string $name, MockPsModule $instance): void
    {
        static::$instances[$name] = $instance;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
