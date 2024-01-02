<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\OrderSettings;

use Gett\MyparcelBE\Adapter\DeliveryOptionsFromDefaultExportSettingsAdapter;
use Gett\MyparcelBE\DeliveryOptions\DefaultExportSettingsRepository;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsMerger;
use Gett\MyparcelBE\DeliverySettings\DeliverySettingsRepository;
use Gett\MyparcelBE\DeliverySettings\ExtraOptions;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\WeightService;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;

class OrderSettings
{
    private const DIGITAL_STAMP_WEIGHT_RANGES = [
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
            'max'     => 350,
            'average' => 200,
        ],
        [
            'min'     => 350,
            'max'     => 2000,
            'average' => 1175,
        ],
    ];

    /**
     * @var null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    private $deliveryOptions;

    /**
     * @var \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    private $extraOptions;

    /**
     * @var array
     */
    private $labelOptions;

    /**
     * @var \Gett\MyparcelBE\Model\Core\Order
     */
    private $order;

    /**
     * @var int
     */
    private $orderWeight;

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getDeliveryOptions(): ?AbstractDeliveryOptionsAdapter
    {
        if (! $this->deliveryOptions) {
            $defaultExportSettings = DefaultExportSettingsRepository::getInstance()
                ->getByCarrier($this->order->getIdCarrier());

            $defaults  = new DeliveryOptionsFromDefaultExportSettingsAdapter($defaultExportSettings);
            $fromOrder = DeliverySettingsRepository::getDeliveryOptionsByCartId(
                $this->order->getIdCart()
            );

            $this->deliveryOptions = DeliveryOptionsMerger::create($defaults, $fromOrder);
        }

        return $this->deliveryOptions;
    }

    /**
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    public function getExtraOptions(): ExtraOptions
    {
        if (! $this->extraOptions) {
            $weightService = new WeightService();

            $extraOptions       = DeliverySettingsRepository::getExtraOptionsByCartId($this->order->getIdCart());
            $this->extraOptions = new ExtraOptions([
                'labelAmount'        => $extraOptions->getLabelAmount(),
                'digitalStampWeight' =>
                    $extraOptions->getDigitalStampWeight()
                    ?? $weightService->convertToDigitalStamp(
                        min($this->getOrderWeight(), 2000),
                        self::DIGITAL_STAMP_WEIGHT_RANGES
                    ),
            ]);
        }

        return $this->extraOptions;
    }

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getLabelOptions(): array
    {
        if (! $this->labelOptions) {
            $this->labelOptions = (new LabelOptionsResolver())->getLabelOptions($this->order);
        }

        return $this->labelOptions;
    }

    /**
     * @return \Gett\MyparcelBE\Model\Core\Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return int
     */
    public function getOrderWeight(): int
    {
        $weightService = new WeightService();

        if (! $this->orderWeight) {
            $this->orderWeight = $weightService->convertToGrams($this->order->getTotalWeight());
        }

        return $this->orderWeight;
    }
}
