<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Gett\MyparcelBE\Boot;

/**
 * @extends \MyParcelBE
 */
trait DisplayBackOfficeHeader
{
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->context->controller->addCSS($this->_path . 'views/css/myparceladmin.css');

        if (! Boot::useDevJs()) {
            $this->context->controller->addJS($this->_path . 'views/dist/js/admin/app.js');
            $this->context->controller->addJS($this->_path . 'views/dist/js/admin/chunks/chunk-vendors.js');
        }
    }
}
