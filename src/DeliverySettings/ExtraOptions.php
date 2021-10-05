<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

class ExtraOptions
{
    private $labelAmount;

    private $digitalStampWeight;

    public const DEFAULT_LABEL_AMOUNT = 1;

    public function __construct(array $extraOptions = [])
    {
        $this->labelAmount        = (int) ($extraOptions['labelAmount'] ?? self::DEFAULT_LABEL_AMOUNT);
        $this->digitalStampWeight = (int) ($extraOptions['digitalStampWeight'] ?? 0) ?: null;
    }

    /**
     * @return int
     */
    public function getLabelAmount(): int
    {
        return $this->labelAmount;
    }

    /**
     * @param int $labelAmount
     */
    public function setLabelAmount(int $labelAmount): void
    {
        $this->labelAmount = $labelAmount;
    }

    /**
     * @return int
     */
    public function getDigitalStampWeight(): ?int
    {
        return $this->digitalStampWeight;
    }

    /**
     * @param int $digitalStampWeight
     */
    public function setDigitalStampWeight(int $digitalStampWeight): void
    {
        $this->digitalStampWeight = $digitalStampWeight;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'labelAmount'        => $this->labelAmount,
            'digitalStampWeight' => $this->digitalStampWeight,
        ];
    }
}
