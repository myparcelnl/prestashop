<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Exception;
use MyParcelNL\PrestaShop\DeliveryOptions\DeliveryOptionsManager;
use MyParcelNL\PrestaShop\Factory\OrderSettingsFactory;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Pdk\Facade\OrderLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
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

            /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository $repository */
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
