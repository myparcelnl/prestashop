<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;

class PsCronService implements CronServiceInterface
{
    /**
     * @param  callable $callback
     * @param           ...$args
     *
     * @return void
     */
    public function dispatch(callable $callback, ...$args): void
    {
        $callback(...$args);
    }

    public function schedule(callable $callback, int $timestamp, ...$args): void
    {
        // TODO: Implement schedule() method.
    }
}
