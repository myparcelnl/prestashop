<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use MyParcelNL\Sdk\src\Support\Arr;

class WeightService
{
    public const DIGITAL_STAMP_RANGES = [
        [
            'min'     => 0,
            'max'     => 20,
            'average' => 15,
        ],
        [
            'min'     => 20,
            'max'     => 50,
            'average' => 35,
        ],
        [
            'min'     => 50,
            'max'     => 100,
            'average' => 75,
        ],
        [
            'min'     => 100,
            'max'     => 350,
            'average' => 225,
        ],
        [
            'min'     => 350,
            'max'     => 2000,
            'average' => 1175,
        ],
    ];

    /**
     * @return self
     */
    public static function convertToDigitalStampWeight(int $weight): int
    {
        $results = Arr::where(
            self::DIGITAL_STAMP_RANGES,
            static function ($range) use ($weight) {
                return $weight > $range['min'];
            }
        );

        if (empty($results)) {
            $digitalStampRangeWeight = Arr::first(self::DIGITAL_STAMP_RANGES)['average'];
        } else {
            $digitalStampRangeWeight = Arr::last($results)['average'];
        }

        return $digitalStampRangeWeight;
    }
}
