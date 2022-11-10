<?php

declare(strict_types=1);

/**
 * @property \MyParcelNL $module
 */
class AdminMyParcelBEController extends ModuleAdminController
{
    public function init()
    {
        Tools::redirectAdmin($this->module->getBaseUrl());
    }
}
