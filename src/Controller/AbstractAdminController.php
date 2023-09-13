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
        parent::__construct();

        // Trigger PDK setup
        new MyParcelNL();
    }
}
