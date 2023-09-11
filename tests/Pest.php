<?php

declare(strict_types=1);

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\SharedFactoryState;
use MyParcelNL\Pdk\Tests\Uses\ClearContainerCache;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntityManager;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use function MyParcelNL\Pdk\Tests\usesShared;

require __DIR__ . '/../vendor/myparcelnl/pdk/tests/Pest.php';
require __DIR__ . '/mock_namespaced_class_map.php';
require __DIR__ . '/mock_class_map.php';

const _PS_VERSION_  = '8.0.0';
const _PS_MODE_DEV_ = false;

usesShared(new ClearContainerCache())->in(__DIR__);

uses()
    ->afterEach(function () {
        MockPsEntityManager::reset();
        MockPsEntities::reset();
        MockPsModule::reset();
        MockPsPdkBootstrapper::reset();

        /** @var SharedFactoryState $sharedState */
        $sharedState = Pdk::get(SharedFactoryState::class);
        $sharedState->reset();
    })
    ->in(__DIR__);
