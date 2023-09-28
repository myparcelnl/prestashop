<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Frontend\Service;

use AdminControllerCore;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;

final class PsScriptService extends ScriptService
{
    public const LIB_VUE_DEMI = 'vue-demi';
    public const LIB_VUE      = 'vue';

    /**
     * @param  \AdminControllerCore $controller
     * @param  string               $path
     *
     * @return void
     */
    public function addForAdminHeader(AdminControllerCore $controller, string $path): void
    {
        $this->addVue($controller, Pdk::get('vueVersion'));
        $this->addVueDemi($controller, Pdk::get('vueDemiVersion'));

        /** use new-theme */
        $controller->addCSS(
            __PS_BASE_URI__ . $controller->admin_webpath . '/themes/new-theme/public/theme.css',
            'all',
            1
        );

        $controller->addCSS($path . 'views/js/backend/admin/dist/style.css');
        $controller->addJS($path . 'views/js/backend/admin/dist/index.iife.js');
    }

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
                $isVue3 = version_compare($version, '3.0.0', '>=');
                $file   = $isVue3 ? 'vue.global' : 'vue';

                return $this->createCdnUrl(self::LIB_VUE, $version, Pdk::isDevelopment() ? "$file.js" : "$file.min.js");

            case self::LIB_VUE_DEMI:
                $filename = Pdk::isDevelopment() ? 'index.iife.js' : 'index.iife.min.js';

                return $this->createCdnUrl(self::LIB_VUE_DEMI, $version, $filename);
        }

        return null;
    }

    /**
     * @param  \AdminControllerCore $controller
     * @param  string               $version
     *
     * @return void
     */
    protected function addVue(AdminControllerCore $controller, string $version): void
    {
        $controller->addJS($this->getCdnUrl(self::LIB_VUE, $version), false);
    }

    /**
     * @param  \AdminControllerCore $controller
     * @param  string               $version
     *
     * @return void
     */
    protected function addVueDemi(AdminControllerCore $controller, string $version): void
    {
        $controller->addJS($this->getCdnUrl(self::LIB_VUE_DEMI, $version), false);
    }
}
