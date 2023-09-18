<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Grid\Column\MyParcelOrderColumn;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

trait HasPdkRenderHooks
{
    /**
     * Add the "MyParcel" column to the order grid to render the order boxes in.
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addBefore(Pdk::get('orderColumnBefore'), new MyParcelOrderColumn());
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
                /** @var PdkOrderRepositoryInterface $repository */
                $repository = Pdk::get(PdkOrderRepositoryInterface::class);
                $order      = $repository->get($row['id_order']);

                $row['myparcel'] = Frontend::renderOrderListItem($order);

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
        $html = Frontend::renderNotifications();
        $html .= Frontend::renderModals();

        return $html;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function hookDisplayAdminEndContent(): string
    {
        return Frontend::renderInitScript();
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
        /** @var PdkOrderRepositoryInterface $repository */
        $repository = Pdk::get(PdkOrderRepositoryInterface::class);
        $order      = $repository->get($params['id_order']);

        return Frontend::renderOrderBox($order);
    }
}
