<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;

class PsScriptService extends ScriptService
{
    protected const LIB_VUE_DEMI = 'vue-demi';
    protected const LIB_VUE      = 'vue';

    /**
     * @param  string $package
     * @param  string $version
     *
     * @return null|string
     */
    public function getCdnUrl(string $package, string $version): ?string
    {
        switch ($package) {
            case self::LIB_VUE:
                $filename = Pdk::isDevelopment() ? 'vue.global.js' : 'vue.global.min.js';

                return $this->createCdnUrl(self::LIB_VUE, $version, "dist/$filename");

            case self::LIB_VUE_DEMI:
                $filename = Pdk::isDevelopment() ? 'index.iife.js' : 'index.iife.min.js';

                return $this->createCdnUrl(self::LIB_VUE_DEMI, $version, $filename);
        }

        return null;
    }

    /**
     * @param  string $version
     *
     * @return null|string
     */
    protected function getVueCdnUrl(string $version): ?string
    {
        return $this->getCdnUrl(self::LIB_VUE, $version);
    }

    /**
     * @param  string $version
     *
     * @return null|string
     */
    protected function getVueDemiCdnUrl(string $version): ?string
    {
        return $this->getCdnUrl(self::LIB_VUE_DEMI, $version);
    }
}
