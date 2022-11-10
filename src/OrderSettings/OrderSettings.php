<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\OrderSettings;

use MyParcelNL\PrestaShop\Adapter\DeliveryOptionsFromDefaultExportSettingsAdapter;
use MyParcelNL\PrestaShop\DeliveryOptions\DefaultExportSettingsRepository;
use MyParcelNL\PrestaShop\DeliverySettings\DeliverySettingsRepository;
use MyParcelNL\PrestaShop\DeliverySettings\ExtraOptions;
use MyParcelNL\PrestaShop\Label\LabelOptionsResolver;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Service\WeightService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Service\DeliveryOptionsMerger;

class OrderSettings
{
    /**
     * @var null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     */
    private $deliveryOptions;

    /**
     * @var \MyParcelNL\PrestaShop\DeliverySettings\ExtraOptions
     */
    private $extraOptions;

    /**
     * @var array
     */
    private $labelOptions;

    /**
     * @var \MyParcelNL\PrestaShop\Model\Core\Order
     */
    private $order;

    /**
     * @var int
     */
    private $orderWeight;

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order                   $order
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     */
    public function __construct(Order $order, DeliveryOptions $deliveryOptions = null)
    {
        $this->order           = $order;
        $this->deliveryOptions = $deliveryOptions;
    }

    /**
     * @return null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getDeliveryOptions(): ?DeliveryOptions
    {
        if (! $this->deliveryOptions) {
            /** @var \MyParcelNL\PrestaShop\DeliveryOptions\DefaultExportSettingsRepository $repository */
            $repository            = Pdk::get(DefaultExportSettingsRepository::class);
            $defaultExportSettings = $repository
                ->getByCarrier($this->order->getIdCarrier());

            $defaults  = new DeliveryOptionsFromDefaultExportSettingsAdapter($defaultExportSettings);
            $fromOrder = DeliverySettingsRepository::getDeliveryOptionsByCartId(
                $this->order->getIdCart()
            );

            $this->deliveryOptions = DeliveryOptionsMerger::create([$defaults, $fromOrder]);
        }

        return $this->deliveryOptions;
    }

    /**
     * @return \MyParcelNL\PrestaShop\DeliverySettings\ExtraOptions
     * @throws \MyParcelNL\Sdk\src\Exception\ValidationException
     */
    public function getExtraOptions(): ExtraOptions
    {
        if (! $this->extraOptions) {
            $extraOptions       = DeliverySettingsRepository::getExtraOptionsByCartId($this->order->getIdCart());
            $this->extraOptions = new ExtraOptions([
                'labelAmount'        => $extraOptions->getLabelAmount(),
                'digitalStampWeight' =>
                        $extraOptions->getDigitalStampWeight()
                        ?? WeightService::convertToDigitalStamp(min($this->getOrderWeight(), 2000)),
            ]);
        }

        return $this->extraOptions;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getLabelOptions(): array
    {
        if (! $this->labelOptions) {
            /** @var \MyParcelNL\PrestaShop\Label\LabelOptionsResolver $labelOptionsResolver */
            $labelOptionsResolver = Pdk::get(LabelOptionsResolver::class);
            $this->labelOptions   = $labelOptionsResolver->getLabelOptions($this->order);
        }

        return $this->labelOptions;
    }

    /**
     * @return \MyParcelNL\PrestaShop\Model\Core\Order
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
        if (! $this->orderWeight) {
            $this->orderWeight = WeightService::convertToGrams($this->order->getTotalWeight());
        }

        return $this->orderWeight;
    }
}
