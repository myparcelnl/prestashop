<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use AdminController;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Script\Contract\PsScriptServiceInterface;

final class PsBackendScriptService extends AbstractPsScriptService implements PsScriptServiceInterface
{
    /**
     * @param  \AdminController $controller
     * @param  string           $path
     *
     * @return void
     */
    public function register($controller, string $path): void
    {
        $this->addVue($controller, Pdk::get('vueVersion'));
        $this->addVueDemi($controller, Pdk::get('vueDemiVersion'));

        /** use new-theme */
        $controller->addCSS(
            sprintf('%s%s/themes/new-theme/public/theme.css', __PS_BASE_URI__, $controller->admin_webpath),
            'all',
            1
        );

        $controller->addCSS("{$path}views/js/backend/admin/dist/style.css");
        $controller->addJS("{$path}views/js/backend/admin/dist/index.iife.js");
    }

    /**
     * @param  \AdminController $controller
     * @param  string           $version
     *
     * @return void
     */
    protected function addVue(AdminController $controller, string $version): void
    {
        $controller->addJS($this->getVueCdnUrl($version), false);
    }

    /**
     * @param  \AdminController $controller
     * @param  string           $version
     *
     * @return void
     */
    protected function addVueDemi(AdminController $controller, string $version): void
    {
        $controller->addJS($this->getVueDemiCdnUrl($version), false);
    }
}
