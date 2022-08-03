<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Pdk\Facade\OrderLogger;
use Gett\MyparcelBE\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\RenderService;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use Throwable;

trait HasPdkRenderHooks
{
    /**
     * Renders MyParcel buttons in order grid.
     *
     * @param  array $params
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function hookActionOrderGridPresenterModifier(array &$params): void
    {
        $params['presented_grid']['data']['records'] = new RecordCollection(
            array_map(static function (array $row) {
                /** @var PdkOrderRepository $repository */
                $repository = Pdk::get(PdkOrderRepository::class);
                $order      = $repository->get($row['id_order']);

                $row['myparcel'] = RenderService::renderOrderListColumn($order);

                return $row;
            }, $params['presented_grid']['data']['records']->all())
        );
    }

    /**
     * Renders the notification area.
     *
     * @return string
     */
    public function hookDisplayAdminAfterHeader(): string
    {
        return RenderService::renderNotifications();
    }

    /**
     * Renders the initialisation script.
     *
     * @throws \Exception
     */
    public function hookDisplayAdminEndContent(): string
    {
        $controller = \Tools::getValue('controller');
        $html       = RenderService::renderInitScript();

        if (in_array($controller, ['AdminOrders'])) {
            $html .= RenderService::renderModals();
        }

        return $html;
    }

    /**
     * Renders the shipment card on a single order page.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderMain(array $params): string
    {
        try {
            /** @var \Gett\MyparcelBE\Pdk\Order\Repository\PdkOrderRepository $repository */
            $repository = Pdk::get(PdkOrderRepository::class);
            $order      = $repository->get($params['id_order']);

            return RenderService::renderOrderCard($order);
        } catch (Throwable $exception) {
            OrderLogger::error('Failed to render order card.', [
                'exception' => $exception,
                'order'     => $params['id_order'],
            ]);

            return '';
        }
    }
}
