<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;

abstract class MockPsCustomerMessage extends ObjectModel
{
    /**
     * @param  int $orderId
     *
     * @return array
     */
    public static function getMessagesByOrderId(int $orderId): array
    {
        return MockPsObjectModels::getByClass(static::class)
            ->filter(static function (array $message) use ($orderId) {
                return $message['id_order'] === $orderId;
            })
            ->all();
    }
}
