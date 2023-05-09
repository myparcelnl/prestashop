<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Installer;

defined('_PS_VERSION_') or die();

function upgrade_module_2_0_0(MyParcelNL $module): bool
{
    try {
        Installer::install();
    } catch (Throwable $e) {
        return false;
    }

    return true;
}
