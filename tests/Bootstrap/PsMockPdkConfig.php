<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\autowire;

class PsMockPdkConfig extends MockPdkConfig
{
    /**
     * @param  null|array $config
     *
     * @return array
     */
    public static function create(?array $config = []): array
    {
        return parent::create(
            array_merge(
                [
                    'ps.entityManager'                => autowire(MockEntityManager::class),
                    AbstractPdkOrderRepository::class => autowire(MockPdkOrderRepository::class),
                ],
                $config
            )
        );
    }
}
