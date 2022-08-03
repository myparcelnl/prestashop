<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\OrderSettings;

use Gett\MyparcelBE\Adapter\DeliveryOptionsFromDefaultExportSettingsAdapter;
use Gett\MyparcelBE\DeliveryOptions\DefaultExportSettingsRepository;
use Gett\MyparcelBE\DeliverySettings\DeliverySettingsRepository;
use Gett\MyparcelBE\DeliverySettings\ExtraOptions;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\WeightService;
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
     * @param  \Gett\MyparcelBE\Model\Core\Order                   $order
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
            /** @var \Gett\MyparcelBE\DeliveryOptions\DefaultExportSettingsRepository $repository */
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
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
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
            /** @var \Gett\MyparcelBE\Label\LabelOptionsResolver $labelOptionsResolver */
            $labelOptionsResolver = Pdk::get(LabelOptionsResolver::class);
            $this->labelOptions   = $labelOptionsResolver->getLabelOptions($this->order);
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
        if (! $this->orderWeight) {
            $this->orderWeight = WeightService::convertToGrams($this->order->getTotalWeight());
        }

        return $this->orderWeight;
    }
}
