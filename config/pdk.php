<?php

declare(strict_types=1);

use Gett\MyparcelBE\Pdk\Logger\DefaultLogger;
use Gett\MyparcelBE\Pdk\Logger\OrderLogger;
use Gett\MyparcelBE\Pdk\Storage\DatabaseStorage;

return [
    'storage' => [
        'default' => DatabaseStorage::class,
    ],
    'logger'  => [
        'default' => DefaultLogger::class,
        'order'   => OrderLogger::class,
    ],
];
