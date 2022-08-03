<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Grid\Action\Bulk\IconBulkAction;
use Gett\MyparcelBE\Grid\Column\LabelsColumn;
use MyParcelNL\Pdk\Facade\LanguageService;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;

trait OrdersGridHooks
{
    /**
     * Extends order grid hooks. Adds custom columns and bulk actions.
     *
     * @param  array $params
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
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
                    ->setName(LanguageService::translate($data['label']))
                    ->setOptions(['material_icon' => $data['icon']])
            );
        }
    }

    /**
     * @param  array $params
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        $allowedCarriers = array_map('intval', [
            Configuration::get(Constant::DPD_CONFIGURATION_NAME),
            Configuration::get(Constant::BPOST_CONFIGURATION_NAME),
            Configuration::get(Constant::POSTNL_CONFIGURATION_NAME),
        ]);
        $carrierIds      = implode(',', $allowedCarriers);
        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        $prefix = 'car' . $this->id;
        $searchQueryBuilder->addSelect(sprintf('IF(%s.id_reference IN(%s), 1, 0) AS labels', $prefix, $carrierIds));
        $searchQueryBuilder->addSelect(sprintf('o.id_carrier, %s.id_reference AS id_carrier_reference', $prefix));
        $searchQueryBuilder->addSelect(sprintf('IFNULL(%s.name, \'\') AS delivery_info', $prefix));
        $searchQueryBuilder->addSelect('o.id_cart');
        $searchQueryBuilder->leftJoin(
            'o',
            Table::withPrefix('carrier'),
            $prefix,
            sprintf('o.id_carrier = %s.id_carrier', $prefix)
        );
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
                'label' => 'action_print',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::refreshLabels */
            'refresh' => [
                'icon'  => 'refresh',
                'label' => 'action_refresh',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::export */
            'export'        => [
                'icon'  => 'add',
                'label' => 'action_export',
            ],
            /** @see \Gett\MyparcelBE\Controllers\Admin\AdminMyParcelOrderController::exportPrint */
            'exportPrint'   => [
                'icon'  => 'print',
                'label' => 'action_export_and_print',
            ],
        ];
    }
}
