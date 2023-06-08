<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use AdminControllerCore;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;

class PsScriptService extends ScriptService
{
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

        $controller->addCSS($path . 'views/js/admin/lib/style.css');
        $controller->addJS($path . 'views/js/admin/lib/prestashop-admin.iife.js');
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    public function addVue(AdminControllerCore $controller, string $version): void
    {
        $isVue3   = version_compare($version, '3.0.0', '>=');
        $file     = $isVue3 ? 'vue.global' : 'vue';
        $filename = Pdk::isDevelopment() ? "$file.js" : "$file.min.js";

        $controller->addJS($this->createCdnUrl('vue', $version, $filename), false);
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    public function addVueDemi(AdminControllerCore $controller, string $version): void
    {
        $filename = Pdk::isDevelopment() ? 'index.iife.js' : 'index.iife.min.js';

        $controller->addJS($this->createCdnUrl('vue-demi', $version, $filename), false);
    }
}
