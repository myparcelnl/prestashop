<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use Configuration;
use DI\ContainerBuilder;
use Link;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use Smarty;
use function DI\get;

abstract class MockPsContext extends BaseMock implements StaticMockInterface
{
    protected static $instance;

    /**
     * @var \DI\Container
     */
    public $container;

    /**
     * @var Link
     */
    public $link;

    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setupContainer();

        $this->link   = new Link();
        $this->smarty = new Smarty();
    }

    public static function getContext(): MockPsContext
    {
        if (! static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function setupContainer(): void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'doctrine.orm.entity_manager'             => get(MockPsEntityManager::class),
            'prestashop.adapter.legacy.configuration' => get(Configuration::class),
            'prestashop.core.admin.tab.repository'    => get(MockPsTabRepository::class),
            'ps.entityManager'                        => get(MockPsEntityManager::class),
        ]);

        $this->container = $builder->build();
    }
}


