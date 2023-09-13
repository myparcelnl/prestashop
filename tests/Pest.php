<?php

declare(strict_types=1);

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

use MyParcelNL\Pdk\Tests\Uses\ClearContainerCache;
use MyParcelNL\PrestaShop\Tests\TestCase;
use function MyParcelNL\Pdk\Tests\usesShared;

/** @see \MyParcelNL\PrestaShop\bootPdk() */
const PEST = true;

require __DIR__ . '/../vendor/myparcelnl/pdk/tests/Pest.php';
require __DIR__ . '/mock_namespaced_class_map.php';
require __DIR__ . '/mock_class_map.php';

const _PS_VERSION_  = '8.0.0';
const _PS_MODE_DEV_ = false;
const _PS_ROOT_DIR_ = __DIR__ . '/../';

usesShared(new ClearContainerCache())->in(__DIR__);

uses(TestCase::class)->in(__DIR__);
