<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Tests\Bootstrap\TestCase as PdkTestCase;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Mock\MockItems;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsContext;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntityManager;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;

class TestCase extends PdkTestCase
{
    /**
     * @return class-string<StaticMockInterface>[]
     */
    protected function getStaticResetServices(): array
    {
        return [
            MockItems::class,
            MockPsConfiguration::class,
            MockPsContext::class,
            MockPsEntities::class,
            MockPsEntityManager::class,
            MockPsModule::class,
            MockPsObjectModels::class,
            MockPsPdkBootstrapper::class,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        /** @var \MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface $service */
        foreach ($this->getStaticResetServices() as $service) {
            $service::reset();
        }

        Facade::setPdkInstance(null);
    }
}
