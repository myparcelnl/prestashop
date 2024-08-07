<?php

declare(strict_types=1);

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

use MyParcelNL\PrestaShop\Tests\TestCase;

/** @see \MyParcelNL\PrestaShop\bootPdk() */
const PEST = true;

require __DIR__ . '/../vendor/myparcelnl/pdk/tests/Pest.php';
require __DIR__ . '/mock_namespaced_class_map.php';
require __DIR__ . '/mock_class_map.php';
require __DIR__ . '/mock_namespaced_class_map_after.php';

const _PS_ROOT_DIR_     = '/var/www/html';
const _PS_CORE_DIR_     = _PS_ROOT_DIR_;
const _PS_MODULE_DIR_   = _PS_CORE_DIR_ . '/modules/';
const _PS_SHIP_IMG_DIR_ = _PS_CORE_DIR_ . '/s/';

const _DB_PREFIX_        = 'ps_';
const _MYSQL_ENGINE_     = 'InnoDB';
const _PS_MODE_DEV_      = false;
const _PS_VERSION_       = '8.0.0';
const _PS_USE_SQL_SLAVE_ = false;

uses(TestCase::class)->in(__DIR__);

uses()
    ->group('migrations')
    ->in(__DIR__ . '/Unit/Migration');
