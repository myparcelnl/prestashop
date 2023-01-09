<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Storage;

use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Storage\AbstractStorage;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

class DatabaseOrderStorage extends AbstractStorage
{
    public function delete(string $storageKey): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param  string $storageKey
     *
     * @return \MyParcelNL\Pdk\Base\Model\Model|\MyParcelNL\Pdk\Plugin\Model\PdkOrder
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function get(string $storageKey)
    {
        $qb = new \DbQuery();
        $qb->select('*');
        $qb->from('orders');

        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($qb->build());

        $order = new PdkOrder();

        return $order->fill(Arr::only($order->getAttributes(), $data));
    }

    /**
     * @param  string                                $storageKey
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $item
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function set(string $storageKey, $item): void
    {
        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->insert(
                Table::TABLE_ORDER_DATA,
                [
                    'externalIdentifier' => $storageKey,
                    'deliveryOptions'    => json_encode($item->deliveryOptions->toArray()),
                    'shipments'          => json_encode($item->shipments->toArray()),
                ],
                false,
                true,
                Db::REPLACE
            );
    }
}
