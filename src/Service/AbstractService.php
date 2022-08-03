<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use MyParcelBE;

/**
 * Service to use with controllers.
 */
abstract class AbstractService
{
    /**
     * @var \MyParcelBE
     */
    protected $module;

    /**
     * @param  \MyParcelBE $module
     */
    public function __construct(MyParcelBE $module)
    {
        $this->module = $module;
    }
}
