<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Context;
use MyParcelNL;

abstract class AbstractProvider
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
    public function __construct(Context $context = null)
    {
        $this->module  = MyParcelNL::getModule();
        $this->context = $context ?? Context::getContext();
    }
}
