<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Concern;

trait HasInstance
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @param  mixed ...$arguments
     *
     * @return static
     */
    public static function getInstance(...$arguments): self
    {
        if (static::$instance) {
            return static::$instance;
        }

        static::$instance = (new static(...$arguments));

        return static::$instance;
    }
}
