<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

class MockPsDispatcher extends BaseMock implements StaticMockInterface
{
    /**
     * @var null|static
     */
    private static $instance;

    /**
     * @var string
     */
    private static $controller = 'AdminOrders';

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (! self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance   = null;
        self::$controller = 'AdminOrders';
    }

    /**
     * Allows tests to simulate a different current controller.
     */
    public static function setController(string $controller): void
    {
        self::$controller = $controller;
    }

    public function getController(): string
    {
        return self::$controller;
    }
}
