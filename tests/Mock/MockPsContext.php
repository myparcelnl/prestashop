<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use DI\ContainerBuilder;
use Link;
use MyParcelNL\PrestaShop\Tests\Mock\Concern\HasStaticFunctionMocks;
use Smarty;
use function DI\autowire;

abstract class MockPsContext
{
    use HasStaticFunctionMocks;

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

    /**
     * @return void
     * @throws \Exception
     */
    private function setupContainer(): void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'doctrine.orm.entity_manager' => autowire(MockPsEntityManager::class),
        ]);

        $this->container = $builder->build();
    }
}


