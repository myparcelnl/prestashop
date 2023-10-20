<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

// For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md
return [
    'prefix'  => '_MyParcelNL' . bin2hex(random_bytes(8)),
    'finders' => [
        Finder::create()
            ->append([
                'myparcelnl.php',
                'composer.json',
            ]),
        Finder::create()
            ->files()
            ->in(['src', 'config', 'controllers', 'upgrade']),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.lock/')
            ->exclude([
                'test',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('vendor'),
    ],

    'exclude-files' => [
        'vendor/php-di/php-di/src/Compiler/Template.php',
    ],

    'exclude-namespaces' => [
        // Exclude global namespace
        '/^$/',
        'MyParcelNL',
        'PrestaShop',
        'PrestaShopBundle',
    ],
];
