<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Grid\Column\MyParcelOrderColumn;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use Order;
use PrestaShopCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\ButtonBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

/**
 * Modifies the order grid
 */
trait HasPdkOrderGridHooks
{
    /**
     * Extends the order grid actions and columns.
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $this->addColumn($definition);
        $this->addBulkActions($definition);
    }

    /**
     * Renders the pdk order list item in our created MyParcel column.
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
        /** @var PdkOrderRepositoryInterface $repository */
        $repository = Pdk::get(PdkOrderRepositoryInterface::class);
        /** @var PdkOrderCollection $pdkOrders */
        $pdkOrders = $repository->fromOrderGridCollection($params['presented_grid']['data']['records']);

        $records = $params['presented_grid']['data']['records']->all();

        $myParcelCarrierOrderIds = $this->getOrderIdsWithMyParcelCarrier(array_column($records, 'id_order'));

        // Amend the record collection with myparcel data.
        $params['presented_grid']['data']['records'] = new RecordCollection(
            array_map(static function (array $row) use ($pdkOrders, $myParcelCarrierOrderIds) {
                // Find the specific order in the already loaded collection of pdk orders, so we don't have to load it again.
                $order = $pdkOrders->firstWhere('externalIdentifier', (int) $row['id_order']);

                if ($order && self::shouldRenderOrderListItem($order, $myParcelCarrierOrderIds)) {
                    $row['myparcel'] = Frontend::renderOrderListItem($order);
                }

                return $row;
            }, $records)
        );
    }

    /**
     * The widget is hidden for orders placed with a non-MyParcel carrier, so the grid does not
     * offer exporting them. Orders that already have shipments always keep the widget: their
     * label status must stay accessible, and the carrier mapping may have moved to a new
     * id_carrier since the order was placed (PrestaShop versions carriers on edit).
     *
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     * @param  int[]                                    $myParcelCarrierOrderIds
     *
     * @return bool
     */
    private static function shouldRenderOrderListItem(PdkOrder $order, array $myParcelCarrierOrderIds): bool
    {
        return in_array((int) $order->externalIdentifier, $myParcelCarrierOrderIds, true)
            || $order->shipments->isNotEmpty();
    }

    /**
     * Filters the given order ids down to those whose carrier is MyParcel-linked, using one query
     * for the carrier ids and one for the carrier mappings.
     *
     * @param  int[] $orderIds
     *
     * @return int[]
     */
    private function getOrderIdsWithMyParcelCarrier(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        /** @var PsCarrierMappingRepository $carrierMappingRepository */
        $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);

        $mappedCarrierIds = $carrierMappingRepository
            ->all()
            ->map(static function (MyparcelnlCarrierMapping $mapping): int {
                return (int) $mapping->getCarrierId();
            })
            ->toArray();

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_order', 'in', array_map('intval', $orderIds));

        $result = [];

        /** @var \Order $psOrder */
        foreach ($orders->getResults() as $psOrder) {
            if (in_array((int) $psOrder->id_carrier, $mappedCarrierIds, true)) {
                $result[] = (int) $psOrder->id;
            }
        }

        return $result;
    }

    /**
     * @param  \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition
     *
     * @return void
     */
    private function addBulkActions(GridDefinitionInterface $definition): void
    {
        $bulkActions = $definition->getBulkActions();

        foreach (Pdk::get('bulkActions') as $bulkAction) {
            $id     = MyParcelNL::MODULE_NAME . "-$bulkAction";
            $action = new ButtonBulkAction($id);

            $translation = sprintf('MyParcel: %s', Language::translate($bulkAction));

            $action
                ->setName($translation)
                ->setOptions(['class' => $id]);

            $bulkActions->add($action);
        }
    }

    /**
     * @param  \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition
     *
     * @return void
     */
    private function addColumn(GridDefinitionInterface $definition): void
    {
        $definition
            ->getColumns()
            ->addBefore(Pdk::get('orderColumnBefore'), new MyParcelOrderColumn());
    }
}
