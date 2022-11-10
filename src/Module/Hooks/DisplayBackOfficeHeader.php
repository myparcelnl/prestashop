<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

/**
 * @extends \MyParcelNL
 */
trait DisplayBackOfficeHeader
{
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->context->controller->addCSS($this->_path . 'views/css/myparceladmin.css');

        $this->context->controller->addJS($this->_path . 'views/dist/js/admin/prestashop-admin.cjs');
    }
}
