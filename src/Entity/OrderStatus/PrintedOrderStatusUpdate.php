<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity\OrderStatus;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Module\Configuration\Form\OrderForm;

class PrintedOrderStatusUpdate extends AbstractOrderStatusUpdate
{
    /**
     * @return string
     */
    public function getOrderStatusSetting(): string
    {
        return Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function onExecute(): bool
    {
        if (! parent::onExecute()) {
            return false;
        }

        $this->sendEmail(OrderForm::SEND_NOTIFICATION_AFTER_PRINTED);
        return true;
    }
}
