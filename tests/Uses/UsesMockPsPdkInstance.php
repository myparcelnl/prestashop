<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Uses;

use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;

final class UsesMockPsPdkInstance extends UsesEachMockPdkInstance
{
    /**
     * @return void
     */
    public function afterEach(): void
    {
        MockPsPdkBootstrapper::reset();

        parent::afterEach();
    }

    /**
     * @throws \Exception
     */
    protected function setup(): void
    {
        $pluginFile = __DIR__ . '/../../myparcelnl.php';

        MockPsPdkBootstrapper::setConfig(MockPdkConfig::create($this->config));

        MockPsPdkBootstrapper::boot(
            'myparcelnl',
            'MyParcel [TEST]',
            '0.0.1',
            sprintf('%s/', dirname($pluginFile)),
            'https://my-site/'
        );
    }
}
