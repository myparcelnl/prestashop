<?php

declare(strict_types=1);

/**
 * Suppress E_DEPRECATED warnings during test execution on PHP 8.4+.
 *
 * PHP 8.4 deprecated implicitly nullable parameters, which triggers warnings
 * in older vendor packages (Pest v1, Symfony, PHP-DI, etc.) at file-parse time,
 * before any custom error handler can intercept them. This suppresses all
 * E_DEPRECATED globally as a workaround. Our own deprecation issues are
 * caught by PHPStan static analysis instead.
 */
if (PHP_VERSION_ID >= 80400) {
    error_reporting(E_ALL & ~E_DEPRECATED);
}

require __DIR__ . '/../vendor/autoload.php';
