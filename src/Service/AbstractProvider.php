<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Context;
use MyParcelBE;

abstract class AbstractProvider
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
    public function __construct(Context $context = null)
    {
        $this->module  = MyParcelBE::getModule();
        $this->context = $context ?? Context::getContext();
    }
}
