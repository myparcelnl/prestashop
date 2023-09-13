<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Grid\Column\LabelsColumn;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

trait HasPdkRenderHooks
{
    /**
     * Add columns to the order grid to render the order boxes in.
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $appInfo = Pdk::getAppInfo();

        $definition
            ->getColumns()
            ->addBefore(
                'actions',
                (new LabelsColumn($appInfo->name))
                    ->setName($appInfo->title)
            );

        //        $bulkActions = $definition->getBulkActions();
        //        foreach ($this->getBulkActionsMap() as $action => $data) {
        //            $bulkActions->add(
        //                (new IconBulkAction($action))
        //                    ->setName(Language::translate($data['label']))
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
