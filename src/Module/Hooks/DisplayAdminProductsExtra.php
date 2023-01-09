<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use MyParcelNL\PrestaShop\Database\Table;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

trait DisplayAdminProductsExtra
{
    /**
     * @param  array $params
     *
     * @return void
     */
    public function hookActionProductUpdate(array $params): void
    {
        foreach ($_POST as $key => $item) {
            if (0 === stripos($key, $this->name)) {
                Db::getInstance()
                    ->update(
                        Table::TABLE_PRODUCT_SETTINGS,
                        [
                            'id_product' => (int) $params['id_product'],
                            'name'       => $key,
                            'value'      => $item,
                        ],
                        [
                            'id_product' => (int) $params['id_product'],
                            'name'       => $key,
                        ]
                    );
            }
        }
    }

}
