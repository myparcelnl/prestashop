<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL;

/**
 * Service to use with controllers.
 */
abstract class AbstractService
{
    /**
     * @var \MyParcelNL
     */
    protected $module;

    /**
     * @param  \MyParcelNL $module
     */
    public function __construct(MyParcelNL $module)
    {
        $this->module = $module;
    }
}
