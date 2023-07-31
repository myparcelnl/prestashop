<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use CustomerMessage;
use CustomerThread;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;

trait HasPdkOrderHooks
{
    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionObjectCustomerMessageAddAfter(array $params): void
    {
        if ($params['object'] instanceof CustomerMessage) {
            $message = $params['object'];
            $thread  = new CustomerThread($message->id_customer_thread);

            Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
                'orderIds' => [$thread->id_order],
            ]);
        }
    }
}
