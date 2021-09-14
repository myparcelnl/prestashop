<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Configuration;
use Db;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;

trait DisplayAdminProductsExtra
{
    /**
     * @throws \PrestaShopDatabaseException
     */
    public function hookActionProductUpdate(array $params): void
    {
        Db::getInstance()
            ->delete(
                Table::TABLE_PRODUCT_CONFIGURATION,
                'id_product = ' . (int) $params['id_product']
            );
        foreach ($_POST as $key => $item) {
            if (0 === stripos($key, $this->name)) {
                Db::getInstance()
                    ->insert(Table::TABLE_PRODUCT_CONFIGURATION, [
                        'id_product' => (int) $params['id_product'],
                        'name'       => $key,
                        'value'      => $item,
                    ]);
            }
        }
    }

    public function hookDisplayAdminProductsExtra($params): string
    {
        $params = $this->getProductSettings((int) $params['id_product']);

        $this->context->smarty->assign(
            [
                'params' => $params,
                'PACKAGE_TYPE' => Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
                'ONLY_RECIPIENT' => Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
                'AGE_CHECK' => Constant::AGE_CHECK_CONFIGURATION_NAME,
                'PACKAGE_FORMAT' => Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,
                'RETURN_PACKAGE' => Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
                'SIGNATURE_REQUIRED' => Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
                'INSURANCE' => Constant::INSURANCE_CONFIGURATION_NAME,
                'CUSTOMS_FORM' => Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
                'CUSTOMS_CODE' => Constant::CUSTOMS_CODE_CONFIGURATION_NAME,
                'CUSTOMS_ORIGIN' => Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME,
                'CUSTOMS_AGE_CHECK' => Constant::CUSTOMS_AGE_CHECK_CONFIGURATION_NAME,
                'countries' => \Country::getCountries(\Context::getContext()->language->id),
                'isBE' => $this->isBE(),
            ]
        );

        return $this->display($this->name, 'views/templates/admin/hook/products_form.tpl');
    }

    /**
     * @param  int $id_product
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getProductSettings(int $id_product): array
    {
        $table  = Table::withPrefix(Table::TABLE_PRODUCT_CONFIGURATION);
        $result = Db::getInstance()
            ->executeS(
                <<<SQL
SELECT *
FROM $table WHERE id_product = $id_product
SQL
            );
        $return = [];
        foreach ($result as $item) {
            $return[$item['name']] = $item['value'] ?: 0;
        }

        if (! $return[Constant::CUSTOMS_FORM_CONFIGURATION_NAME]) {
            $return[Constant::CUSTOMS_FORM_CONFIGURATION_NAME] = Configuration::get(
                Constant::CUSTOMS_FORM_CONFIGURATION_NAME
            );
        }

        if (! $return[Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME]) {
            $return[Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME] = Configuration::get(
                Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME
            );
        }

        return $return;
    }
}
