<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use CustomerMessage;
use CustomerThread;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;

trait HasPdkOrderHooks
{
    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionObjectCustomerMessageAddAfter(array $params): void
    {
        if (! $params['object'] instanceof CustomerMessage) {
            return;
        }

        $message = $params['object'];
        $thread  = new CustomerThread($message->id_customer_thread);

        Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
            'orderIds' => [$thread->id_order],
        ]);
    }

    /**
     * Renders the order box on a single order page.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderMain(array $params): string
    {
        /** @var PdkOrderRepositoryInterface $repository */
        $repository = Pdk::get(PdkOrderRepositoryInterface::class);
        $order      = $repository->get($params['id_order']);

        return Frontend::renderOrderBox($order);
    }

    /**
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderLeft(array $params): string
    {
        // This hook can be used for order left sidebar functionality
        // For now, return empty string to avoid breaking the installation
        return '';
    }

    /**
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderMainBottom(array $params): string
    {
        // This hook can be used for order main bottom functionality
        // For now, return empty string to avoid breaking the installation
        return '';
    }
}
