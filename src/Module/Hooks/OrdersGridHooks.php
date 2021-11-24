<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Grid\Action\Bulk\IconBulkAction;
use Gett\MyparcelBE\Grid\Column\LabelsColumn;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Hooks\Helpers\AdminOrderList;
use Gett\MyparcelBE\Module\Hooks\Helpers\AdminOrderView;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

trait OrdersGridHooks
{
    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        $allowedCarriers = array_map('intval', [
            Configuration::get(Constant::DPD_CONFIGURATION_NAME),
            Configuration::get(Constant::BPOST_CONFIGURATION_NAME),
            Configuration::get(Constant::POSTNL_CONFIGURATION_NAME),
        ]);
        $carrierIds = implode(',', $allowedCarriers);
        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        $prefix = 'car' . $this->id;
        $searchQueryBuilder->addSelect('IF(' . $prefix . '.id_reference IN(' . $carrierIds . '), 1, 0) AS labels');
        $searchQueryBuilder->addSelect('o.id_carrier, ' . $prefix . '.id_reference AS id_carrier_reference');
        $searchQueryBuilder->addSelect('IFNULL(' . $prefix . '.name, \'\') AS delivery_info');
        $searchQueryBuilder->addSelect('o.id_cart');
        $searchQueryBuilder->leftJoin(
            'o',
            Table::withPrefix('carrier'),
            $prefix,
            'o.id_carrier = ' . $prefix . '.id_carrier'
        );
    }

    /**
     * Extends order grid hooks. Adds custom columns and bulk actions.
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        $promptForLabelPosition = Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME);

        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addBefore(
                'actions',
                (new LabelsColumn('labels'))
                    ->setName($this->l('Labels', 'ordersgridhooks'))
            )
            ->addBefore(
                'labels',
                (new DataColumn('delivery_info'))
                    ->setName($this->l('Delivery date', 'ordersgridhooks'))
                    ->setOptions([
                        'field' => 'delivery_info',
                    ])
            );

        $bulkActions = $definition->getBulkActions();
        foreach ($this->getBulkActionsMap() as $action => $data) {
            $bulkActions->add(
                (new IconBulkAction($action))
                    ->setName(AdminOrderList::getTranslation($data['label']))
                    ->setOptions(['material_icon' => $data['icon']])
            );
        }
    }

    /**
     * Executed when loading order list modal by clicking the "create" button.
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
                $order   = new Order((int) $row['id_order']);
                $service = new AdminPanelRenderService();

                $row['myparcel'] = [
                    AdminPanelRenderService::ID_SHIPMENT_LABELS  => $service->getShipmentLabelsContext($order),
                    AdminPanelRenderService::ID_SHIPMENT_OPTIONS => $service->getShipmentOptionsContext($order),
                ];

                return $row;
            }, $params['presented_grid']['data']['records']->all())
        );
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookDisplayAdminOrderMain($params): string
    {
        return (new AdminOrderView((int) $params['id_order']))->display();
    }

    /**
     * @return array
     */
    private function getBulkActionsMap(): array
    {
        return [
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::print */
            'print'         => [
                'icon'  => 'cloud_download',
                'label' => 'action_print_labels',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::refreshLabels */
            'refreshLabels' => [
                'icon'  => 'refresh',
                'label' => 'action_refresh_labels',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::export */
            'export'        => [
                'icon'  => 'add',
                'label' => 'action_export_labels',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::exportPrint */
            'exportPrint'   => [
                'icon'  => 'print',
                'label' => 'action_export_and_print_labels',
            ],
        ];
    }
}
