<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Exception;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsManager;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Pdk\Facade\OrderLogger;
use Gett\MyparcelBE\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\Pdk\Facade\Pdk;

trait OrderHooks
{
    /**
     * @param  array $params
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function hookActionValidateOrder(array $params): void
    {
        $order = new Order($params['order']->id);

        try {
            $deliveryOptions = OrderSettingsFactory::create($order)
                ->getDeliveryOptions();

            /** @var \Gett\MyparcelBE\Pdk\Order\Repository\PdkOrderRepository $repository */
            $repository = Pdk::get(PdkOrderRepository::class);
            $pdkOrder   = $repository->get($params['order']);

            $pdkOrder->deliveryOptions = $deliveryOptions;

            $repository->update($pdkOrder);

            DeliveryOptionsManager::save($order->getIdCart(), $deliveryOptions);
        } catch (Exception $exception) {
            OrderLogger::error($exception->getMessage(), compact('exception', 'order'));
        }
    }
}
