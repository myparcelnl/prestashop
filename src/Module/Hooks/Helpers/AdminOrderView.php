<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks\Helpers;

use Exception;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Provider\OrderLabelProvider;
use Validate;

class AdminOrderView extends AbstractAdminOrder
{
    /**
     * @var \MyParcelNL\PrestaShop\Model\Core\Order
     */
    private $order;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @param  int $orderId
     *
     * @throws \Exception
     */
    public function __construct(int $orderId)
    {
        parent::__construct();
        $this->orderId = $orderId;
    }

    /**
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function display(): string
    {
        $order = $this->getOrder();

        if (! Validate::isLoadedObject($order)) {
            return '';
        }

        return 'admin order view';
        //        return \Pdk::renderOrderSettings($order);
    }

    /**
     * @return null|array|bool|\mysqli_result|\PDOStatement|resource
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getLabels()
    {
        return (new OrderLabelProvider())->provideLabels($this->orderId, []);
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        try {
            return $this->getOrder()
                ->getTotalWeight();
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    private function getOrder(): Order
    {
        if (! $this->order) {
            $this->order = new Order($this->orderId);
        }

        return $this->order;
    }
}
