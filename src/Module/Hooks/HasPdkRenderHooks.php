<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\RenderServiceInterface;
use MyParcelNL\PrestaShop\Grid\Column\LabelsColumn;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

trait HasPdkRenderHooks
{
    /**
     * Renders the module configuration page.
     *
     * @return string
     */
    public function getContent(): string
    {
        /** @var \MyParcelNL\Pdk\Plugin\Contract\RenderServiceInterface $renderService */
        $renderService = Pdk::get(RenderServiceInterface::class);

        return $renderService->renderPluginSettings();
    }

    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addBefore(
                'actions',
                (new LabelsColumn('myparcel'))
                    ->setName('MyParcel')
            );

        //        $bulkActions = $definition->getBulkActions();
        //        foreach ($this->getBulkActionsMap() as $action => $data) {
        //            $bulkActions->add(
        //                (new IconBulkAction($action))
        //                    ->setName(LanguageService::translate($data['label']))
        //                    ->setOptions(['icon' => $data['icon']])
        //            );
        //        }
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

                $row['myparcel'] = RenderService::renderOrderListItem($order);

                return $row;
            }, $params['presented_grid']['data']['records']->all())
        );
    }

    /**
     * Renders the notification area.
     *
     * @noinspection PhpUnused
     * @return string
     */
    public function hookDisplayAdminAfterHeader(): string
    {
        $html = RenderService::renderNotifications();
        $html .= RenderService::renderModals();

        return $html;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function hookDisplayAdminEndContent(): string
    {
        return RenderService::renderInitScript();
    }

    /**
     * Renders the shipment card on a single order page.
     *
     * @param  array $params
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookDisplayAdminOrderMain(array $params): string
    {
        /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository $repository */
        $repository = Pdk::get(PdkOrderRepository::class);
        $order      = $repository->get($params['id_order']);

        return RenderService::renderOrderBox($order);
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
        /** @var \MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository $repository */
        $repository = Pdk::get(PdkProductRepository::class);
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
        /** @var ScriptServiceInterface $scriptService */
        $scriptService = Pdk::get(ScriptServiceInterface::class);

        /** @var \AdminLegacyLayoutControllerCore $controller */
        $controller = $this->context->controller;

        if (Pdk::isDevelopment()) {
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.js');
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue-demi/0.13.11/index.iife.js');
        } else {
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.min.js');
            $controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/vue-demi/0.13.11/index.iife.min.js');
        }

        /** use new-theme */
        $controller->addCSS(
            __PS_BASE_URI__ . $controller->admin_webpath . '/themes/new-theme/public/theme.css',
            'all',
            1
        );

        $controller->addCSS($this->_path . 'views/js/admin/lib/style.css');
        $controller->addJS($this->_path . 'views/js/admin/lib/prestashop-admin.iife.js');
    }
}
