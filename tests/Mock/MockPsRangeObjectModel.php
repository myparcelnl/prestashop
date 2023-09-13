<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;

abstract class MockPsRangeObjectModel extends ObjectModel
{
    public static function getRanges(int $carrierId): array
    {
        return MockPsObjectModels::getByClass(static::class)
            ->filter(function (object $range) use ($carrierId) {
                return $range->id_carrier === $carrierId;
            })
            ->values()
            ->toArray();
    }
}
