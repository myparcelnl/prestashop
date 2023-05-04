<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Concern;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Support\Str;

trait NeedsSettingsKey
{
    /**
     * @param  string $key
     *
     * @return string
     */
    protected function getOptionName(string $key): string
    {
        $appInfo = Pdk::getAppInfo();

        return strtr('_:plugin_:name', [
            ':plugin' => $appInfo['name'],
            ':name'   => Str::snake($key),
        ]);
    }
}
