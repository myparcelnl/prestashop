<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity\OrderStatus;

use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Entity\OrderStatusUpdateInterface;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Module\Tools\Tools;
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
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function onExecute(): void
    {
        $newOrderStatus = $this->getNewOrderStatus();

        if (! $newOrderStatus) {
            ApiLogger::addLog(
                sprintf(
                    "Order status for %d won't be updated because setting %s is not set.",
                    $this->shipmentId,
                    $this->getOrderStatusSetting()
                )
            );
            return;
        }

        OrderLabel::setOrderStatus($this->shipmentId, $newOrderStatus);
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
            } catch (Exception $e) {
                ApiLogger::addLog($e, ApiLogger::ERROR);
            }
        }
    }
}
