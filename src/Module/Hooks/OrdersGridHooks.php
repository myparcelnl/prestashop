<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Configuration;
use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Grid\Action\Bulk\IconBulkAction;
use MyParcelNL\PrestaShop\Grid\Column\LabelsColumn;
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
                    ->setName(LanguageService::translate('column_labels'))
            )
//            ->addBefore(
//                'labels',
//                (new DataColumn('delivery_info'))
//                    ->setName(LanguageService::translate('column_delivery'))
////                    ->setOptions([
////                        'field' => 'delivery_info',
////                    ])
//            )
        ;

        $bulkActions = $definition->getBulkActions();
        foreach ($this->getBulkActionsMap() as $action => $data) {
            $bulkActions->add(
                (new IconBulkAction($action))
                    ->setName(LanguageService::translate($data['label']))
                    ->setOptions(['icon' => $data['icon']])
            );
        }
    }

//    /**
//     * @param  array $params
//     *
//     * @return void
//     * @noinspection PhpUnused
//     */
//    public function hookActionOrderGridQueryBuilderModifier(array $params): void
//    {
//        $allowedCarriers = array_map('intval', [
//            Configuration::get(Constant::DPD_CONFIGURATION_NAME),
//            Configuration::get(Constant::BPOST_CONFIGURATION_NAME),
//            Configuration::get(Constant::POSTNL_CONFIGURATION_NAME),
//        ]);
//        $carrierIds      = implode(',', $allowedCarriers);
//        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
//        $searchQueryBuilder = $params['search_query_builder'];
//
//        $prefix = 'car' . $this->id;
//        $searchQueryBuilder->addSelect(sprintf('IF(%s.id_reference IN(%s), 1, 0) AS labels', $prefix, $carrierIds));
//        $searchQueryBuilder->addSelect(sprintf('o.id_carrier, %s.id_reference AS id_carrier_reference', $prefix));
//        $searchQueryBuilder->addSelect(sprintf('IFNULL(%s.name, \'\') AS delivery_info', $prefix));
//        $searchQueryBuilder->addSelect('o.id_cart');
//        $searchQueryBuilder->leftJoin(
//            'o',
//            Table::withPrefix('carrier'),
//            $prefix,
//            sprintf('o.id_carrier = %s.id_carrier', $prefix)
//        );
//    }

    /**
     * The available bulk actions.
     *
     * @return array
     */
    private function getBulkActionsMap(): array
    {
        return [
            /** @see \MyParcelNL\Pdk\Plugin\Action\Order\ExportOrderAction */
            PdkActions::EXPORT_ORDERS           => [
                'icon'  => 'add',
                'label' => 'action_export',
            ],
            /** @see \MyParcelNL\Pdk\Plugin\Action\Order\PrintOrderAction */
            PdkActions::PRINT_ORDERS            => [
                'icon'  => 'cloud_download',
                'label' => 'action_print',
            ],
            /**
             * @see \MyParcelNL\Pdk\Plugin\Action\Order\ExportOrderAction
             * @see \MyParcelNL\Pdk\Plugin\Action\Order\PrintOrderAction
             */
            'exportPrint' => [
                'icon'  => 'print',
                'label' => 'action_export_print',
            ],
            /** @see \MyParcelNL\Pdk\Plugin\Action\Order\GetOrderAction */
            PdkActions::GET_ORDERS              => [
                'icon'  => 'refresh',
                'label' => 'action_refresh',
            ],
        ];
    }
}
