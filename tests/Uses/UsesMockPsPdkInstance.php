<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Uses;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use function DI\get;

final class UsesMockPsPdkInstance extends UsesEachMockPdkInstance
{
    /**
     * @throws \Exception
     */
    protected function setup(): void
    {
        MockPsPdkBootstrapper::create($this->getConfig());
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        return array_replace(
            $this->config,
            [
                PdkOrderRepositoryInterface::class => get(PsPdkOrderRepository::class),
                PdkProductRepositoryInterface::class => get(PdkProductRepository::class),
            ]
        );
    }
}
