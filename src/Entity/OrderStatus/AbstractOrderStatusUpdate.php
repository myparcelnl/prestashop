<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\OrderStatus;

use Configuration;
use Exception;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use OrderLabel;

abstract class AbstractOrderStatusUpdate implements OrderStatusUpdateInterface
{
    /**
     * @var bool
     */
    protected $sendMail;

    /**
     * @var int
     */
    protected $shipmentId;

    public function __construct(int $shipmentId, bool $sendMail = false)
    {
        $this->shipmentId = $shipmentId;
        $this->sendMail   = $sendMail;
    }

    /**
     * @return int|null
     */
    public function getNewOrderStatus(): ?int
    {
        return Tools::intOrNull(Configuration::get($this->getOrderStatusSetting()));
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function onExecute(): bool
    {
        $newOrderStatus = $this->getNewOrderStatus();

        if (! $newOrderStatus) {
            DefaultLogger::debug(
                'Order status won\'t be updated because setting is not set.',
                [
                    'shipmentId' => $this->shipmentId,
                    'setting'    => $this->getOrderStatusSetting(),
                ]
            );

            return false;
        }

        return OrderLabel::setOrderStatus($this->shipmentId, $newOrderStatus);
    }

    /**
     * @param  string $status
     */
    protected function sendEmail(string $status): void
    {
        $setting            = Configuration::get(Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME);
        $sendAfterFirstScan = $status === $setting;

        if ($sendAfterFirstScan && $this->sendMail) {
            try {
                OrderLabel::sendShippedNotification($this->shipmentId);
            } catch (Exception $exception) {
                DefaultLogger::error($exception->getMessage(), compact('exception'));
            }
        }
    }
}
