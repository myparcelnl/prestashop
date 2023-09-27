<?php

declare(strict_types=1);

function upgrade_module_2_0_0($module): bool
{
    return \MyParcelNL\PrestaShop\Facade\MyParcelModule::install();
}
