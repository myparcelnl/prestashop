<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Module\Hooks\Helpers\AdminOrderList;

trait DisplayBackOfficeHeader
{
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->context->controller->addCSS(
            __PS_BASE_URI__ . $this->context->controller->admin_webpath . '/themes/new-theme/public/theme.css',
            'all',
            1
        );
        $this->context->controller->addCSS($this->_path . 'views/css/myparceladmin.css');

        $this->context->controller->addJS($this->_path . 'views/dist/js/admin/app.js');
        $this->context->controller->addJS($this->_path . 'views/dist/js/admin/chunks/chunk-vendors.js');
    }

    /**
     * @throws \PrestaShopException
     */
    public function hookDisplayAdminAfterHeader(): string
    {
        return (new AdminPanelRenderService())->renderModals();
    }
}
