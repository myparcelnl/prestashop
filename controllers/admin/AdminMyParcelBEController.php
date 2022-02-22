<?php

declare(strict_types=1);

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelBEController extends ModuleAdminController
{
    public function init()
    {
        Tools::redirectAdmin($this->module->baseUrl);
    }
}
