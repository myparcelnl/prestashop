<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\PrestaShop\Pdk\Facade\OrderLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\ProductRepository;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use Throwable;
use Tools;

trait HasPdkRenderHooks
{
    /**
     * Renders the module configuration page.
     *
     * @return string
     */
    public function getContent(): string
    {
        return'henlo';
    }

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
        if (! $this->shouldRenderPdk()) {
            return '';
        }

        $html = RenderService::renderNotifications();
        $html .= RenderService::renderModals();

        return $html;
    }

    /**
     * @return string
     */
    public function hookDisplayAdminEndContent(): string
    {
        if (! $this->shouldRenderPdk()) {
            return '';
        }

        return RenderService::renderInitScript();
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
            /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository $repository */
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

    /**
     * Renders the product settings.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        /** @var \MyParcelNL\PrestaShop\Pdk\Product\Repository\ProductRepository $repository */
        $repository = Pdk::get(ProductRepository::class);
        $product    = $repository->getProduct($params['id_product']);

        return RenderService::renderProductSettings($product);
    }

    /**
     * Load the js and css files of the admin app.
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        /** @var \AdminLegacyLayoutControllerCore $controller */
        $controller = $this->context->controller;

        if (! $this->shouldRenderPdk()) {
            return;
        }

        if (Pdk::isDevelopment()) {
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.js');
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue-demi/0.13.11/index.iife.js');
        } else {
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.min.js');
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue-demi/0.13.11/index.iife.min.js');
        }

        $controller->addCSS($this->_path . 'views/js/admin/lib/style.css');
    }

    /**
     * @return bool
     */
    private function shouldRenderPdk(): bool
    {
        $controller = Tools::getValue('controller');

        // Match only the order overview and the modules page.
        if (! in_array(
            $controller,
            [
                'AdminModules',
                'AdminOrders',
                'AdminProducts',
                Str::replaceLast('Controller', '', \AdminMyParcelNLController::class),
            ],
            true
        )) {
            return false;
        }

        // Match the module configuration page
        if ($controller === 'AdminModules' && Tools::getValue('module_name') !== $this->name) {
            return false;
        }

        return true;
    }
}
