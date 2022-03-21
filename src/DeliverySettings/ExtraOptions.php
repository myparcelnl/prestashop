<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

class ExtraOptions
{
    /**
     * @var int
     */
    private $labelAmount;

    /**
     * @var int|null
     */
    private $digitalStampWeight;

    /**
     * The default label amount for a consignment
     */
    public const DEFAULT_LABEL_AMOUNT = 1;

    /**
     * ExtraOptions constructor.
     *
     * @param array{labelAmount: int, digitalStampWeight: int} $extraOptions
     */
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
