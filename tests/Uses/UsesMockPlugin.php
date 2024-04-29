<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Uses;

use MyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;

final class UsesMockPlugin extends UsesMockPsPdkInstance
{
    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function setup(): void
    {
        if (! class_exists(MyParcelNL::class)) {
            require_once __DIR__ . '/../../myparcelnl.php';
        }

        $module = new MockMyParcelNL();

        MockPsModule::setInstance($module->name, $module);

        $this->addDefaultData();
    }
}
