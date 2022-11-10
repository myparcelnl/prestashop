<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks\Helpers;

use Configuration;
use Context;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdminOrder extends AbstractController
{
    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var \MyParcelNL
     */
    protected $module;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
//        parent::__construct();
        $this->module  = MyParcelNL::getModule();
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
