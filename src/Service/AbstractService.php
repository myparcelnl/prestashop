<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use MyParcelBE;
use PrestaShop\PrestaShop\Adapter\Configuration;

/**
 * Service to use with controllers.
 */
abstract class AbstractService
{
    /**
     * @var \PrestaShop\PrestaShop\Adapter\Configuration
     */
    protected $configuration;

    /**
     * @var \MyParcelBE
     */
    protected $module;

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->module        = MyParcelBE::getModule();
    }
}
