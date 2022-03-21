<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Configuration;
use Context;
use Gett\MyparcelBE\Constant;
use MyParcelBE;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdminOrder extends AbstractController
{
    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var \MyParcelBE
     */
    protected $module;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
//        parent::__construct();
        $this->module  = MyParcelBE::getModule();
        $this->context = Context::getContext();
    }

    /**
     * @return array
     * @throws \PrestaShopException
     */
    public function getLabelDefaultConfiguration(): array
    {
        return Configuration::getMultiple([
            Constant::LABEL_SIZE_CONFIGURATION_NAME,
            Constant::LABEL_POSITION_CONFIGURATION_NAME,
            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
        ]);
    }
}
