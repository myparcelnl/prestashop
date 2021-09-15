<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Module\Hooks\Helpers\AdminOrderList;

trait DisplayBackOfficeHeader
{
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->context->controller->addCSS($this->_path . 'views/css/myparceladmin.css');
        $this->context->controller->addJS($this->_path . 'views/dist/js/admin/myparcelbo.js');
    }

    public function hookDisplayAdminAfterHeader(): string
    {
        // TODO: test compatibility with < PS1.7.7
        if (!$this->isSymfonyContext() || $this->context->controller->php_self != 'AdminOrders') {
            return '';
        }
        $adminOrderList = new AdminOrderList($this);

        return $adminOrderList->getAdminAfterHeader();
    }
}
