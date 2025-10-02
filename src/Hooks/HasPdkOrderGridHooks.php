<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Grid\Column\MyParcelOrderColumn;
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
