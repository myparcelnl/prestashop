<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Module\Facade\ModuleService;
use PrestaShop\PrestaShop\Adapter\Entity\Configuration;
use PrestaShop\PrestaShop\Adapter\Entity\Context;
use PrestaShop\PrestaShop\Adapter\Entity\Country;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

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

    /**
     * @param  array $params
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        $params = $this->getProductSettings((int) $params['id_product']);

        $this->context->smarty->assign(
            [
                'params'             => $params,
                'PACKAGE_TYPE'       => Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
                'ONLY_RECIPIENT'     => Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
                'AGE_CHECK'          => Constant::AGE_CHECK_CONFIGURATION_NAME,
                'PACKAGE_FORMAT'     => Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,
                'RETURN_PACKAGE'     => Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
                'SIGNATURE_REQUIRED' => Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
                'INSURANCE'          => Constant::INSURANCE_CONFIGURATION_NAME,
                'CUSTOMS_FORM'       => Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
                'CUSTOMS_CODE'       => Constant::CUSTOMS_CODE_CONFIGURATION_NAME,
                'CUSTOMS_ORIGIN'     => Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME,
                'countries'          => Country::getCountries(Context::getContext()->language->id),
                'isBE'               => ModuleService::isBE(),
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
        $query = (new DbQuery())
            ->select('*')
            ->from(Table::TABLE_PRODUCT_CONFIGURATION)
            ->where('id_product = ' . $id_product);

        $result = Db::getInstance()
            ->executeS($query);

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
