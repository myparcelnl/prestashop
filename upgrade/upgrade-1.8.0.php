<?php

declare(strict_types=1);

function upgrade_module_1_8_0($module): bool
{
    return \MyParcelNL\PrestaShop\Facade\MyParcelModule::install();
}

