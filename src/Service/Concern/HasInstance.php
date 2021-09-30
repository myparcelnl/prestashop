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
    private static function getInstance(...$arguments): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = (new self(...$arguments));

        return self::$instance;
    }
}
