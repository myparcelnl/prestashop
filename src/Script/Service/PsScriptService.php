<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;

class PsScriptService extends ScriptService
{
    protected const LIB_VUE = 'vue';

    /**
     * @param  string $version
     *
     * @return null|string
     */
    protected function getVueCdnUrl(string $version): ?string
    {
        $filename = Pdk::isDevelopment() ? 'vue.global.js' : 'vue.global.min.js';

        return $this->createCdnUrl(self::LIB_VUE, $version, "dist/$filename");
    }
}
