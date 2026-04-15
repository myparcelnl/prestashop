<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

/**
 * @property \MyParcelNL $module
 */
abstract class AbstractAdminController extends FrameworkBundleAdminController
{
    public function __construct()
    {
        // FrameworkBundleAdminController had a constructor in PS 1.7/8 but it was removed in PS 9.
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }

        // Trigger PDK setup
        new MyParcelNL();
    }
}
