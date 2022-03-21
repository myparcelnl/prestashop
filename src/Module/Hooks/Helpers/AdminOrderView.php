<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Exception;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Hooks\AdminPanelRenderService;
use Gett\MyparcelBE\Provider\OrderLabelProvider;
use Validate;

class AdminOrderView extends AbstractAdminOrder
{
    /**
     * @var \Gett\MyparcelBE\Model\Core\Order
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

        return (new AdminPanelRenderService())->renderOrderSettings($order);
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
            return $this->getOrder()->getTotalWeight();
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
