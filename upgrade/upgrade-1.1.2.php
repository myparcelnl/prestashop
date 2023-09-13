<?php

declare(strict_types=1);

function upgrade_module_1_1_2($module): bool
{
    return \MyParcelNL\PrestaShop\Facade\Module::install();
}
