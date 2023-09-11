<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Arr;

abstract class MockPsCustomerMessage extends MockPsEntity
{
    protected static $messages = [];

    public static function getMessagesByOrderId(int $orderId): array
    {
        return Arr::get(static::$messages, (string) $orderId, []);
    }
}
