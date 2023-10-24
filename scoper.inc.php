<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Prefixes source files. Vendor files are prefixed with scoper.vendor.inc.php. This is to speed up the scoping process
 * as there is no need to re-scope vendor files on every run.
 *
 * @see https://github.com/humbug/php-scoper/blob/master/docs/configuration.md
 */
return [
    'prefix' => '_MyParcelNL',

    'finders' => [
        Finder::create()
            ->append([
                'myparcelnl.php',
                'composer.json',
            ]),
        Finder::create()
            ->files()
            ->in(['src', 'config', 'controllers', 'upgrade']),
    ],

    'exclude-namespaces' => [
        // Exclude global namespace
        '/^$/',

        'Composer',
        'MyParcelNL',

        // Provided by PrestaShop
        'PrestaShop',
        'PrestaShopBundle',
        'Doctrine',
        'Symfony',
    ],
];
