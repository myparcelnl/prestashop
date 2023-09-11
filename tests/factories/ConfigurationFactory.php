<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsFactory;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;

final class ConfigurationFactory extends AbstractPsFactory
{
    public function make(): void
    {
        MockPsConfiguration::setMany($this->resolveAttributes());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->with([
            'PS_WEIGHT_UNIT' => 'g',
        ]);
    }
}
