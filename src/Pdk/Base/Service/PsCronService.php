<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;

class PsCronService implements CronServiceInterface
{
    /**
     * @param  callable|string $callback
     * @param  mixed           ...$args
     *
     * @return void
     */
    public function dispatch($callback, ...$args): void
    {
        $callback(...$args);
    }

    /**
     * @param  callable|string $callback
     * @param  int             $timestamp
     * @param  mixed           ...$args
     *
     * @return void
     */
    public function schedule($callback, int $timestamp, ...$args): void
    {
        // TODO: Implement schedule() method.
    }
}
