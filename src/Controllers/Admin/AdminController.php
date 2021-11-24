<?php

declare(strict_types=1);

if (file_exists(_PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php';
}

/**
 * @property \MyParcelBE $module
 */
class AdminController extends ModuleAdminController
{
    /**
     * @return void
     */
    public function init(): void
    {
        Tools::redirectAdmin($this->module->baseUrl);
    }
}
