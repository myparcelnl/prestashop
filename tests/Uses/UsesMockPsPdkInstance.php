<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Uses;

use CarrierModule;
use Configuration;
use Exception;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\SharedFactoryState;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use Zone;
use function MyParcelNL\PrestaShop\psFactory;

class UsesMockPsPdkInstance extends UsesEachMockPdkInstance
{
    /**
     * @return void
     */
    protected function addDefaultData(): void
    {
        try {
            psFactory(Configuration::class)->make();

            psFactory(Zone::class)
                ->withName('Europe')
                ->store();

            psFactory(Zone::class)
                ->withName('North America')
                ->store();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function reset(): void
    {
        if (Facade::getPdkInstance()) {
            Pdk::get(SharedFactoryState::class)
                ->reset();
        }

        parent::reset();
    }

    protected function setup(): void
    {
        MockPsPdkBootstrapper::boot('pest', 'Pest', '1.0.0', __DIR__ . '/../../', 'APP_URL');
        MockPsModule::setInstance('pest', new CarrierModule());

        $this->addDefaultData();
    }
}
