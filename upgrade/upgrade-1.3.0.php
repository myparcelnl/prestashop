<?php

declare(strict_types=1);

function upgrade_module_1_3_0($module): bool
{
    return \MyParcelNL\PrestaShop\Facade\Module::install();
}
