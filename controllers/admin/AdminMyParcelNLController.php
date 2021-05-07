<?php

if (file_exists(_PS_MODULE_DIR_ . 'myparcelnl/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'myparcelnl/vendor/autoload.php';
}

class AdminMyParcelNLController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        Tools::redirectAdmin($this->module->baseUrl);
    }
}
