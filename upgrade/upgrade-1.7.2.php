<?php

declare(strict_types=1);

function upgrade_module_1_7_2($module): bool
{
    return \MyParcelNL\PrestaShop\Facade\MyParcelModule::install();
}
