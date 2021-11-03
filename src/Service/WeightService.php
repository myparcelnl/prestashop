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
     * @var float|int
     */
    private $weight;

    /**
     * @param  float|int $weight
     */
    public function __construct($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return self
     */
    public function convertToDigitalStampWeight(): self
    {
        $results = Arr::where(
            self::DIGITAL_STAMP_RANGES,
            function ($range) {
                return $this->weight > $range['min'];
            }
        );

        if (empty($results)) {
            $digitalStampRangeWeight = Arr::first(self::DIGITAL_STAMP_RANGES)['average'];
        } else {
            $digitalStampRangeWeight = Arr::last($results)['average'];
        }

        $this->weight = $digitalStampRangeWeight;
        return $this;
    }

    /**
     * Returns the weight of the order in grams.
     *
     * @return self
     */
    public function convertToGrams(): self
    {
        $this->weight = (new OrderTotalWeight())->convertWeightToGrams($this->weight);
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return (int) $this->weight;
    }
}
