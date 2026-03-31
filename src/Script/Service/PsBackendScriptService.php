<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use AdminController;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Script\Contract\PsScriptServiceInterface;

final class PsBackendScriptService extends PsScriptService implements PsScriptServiceInterface
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

        $adminPath = "{$path}views/js/backend/admin";

        $controller->addCSS("$adminPath/dist/style.css");
        $controller->addJS("$adminPath/dist/index.iife.js");

        /** use new-theme */
        // PS 9 replaced admin_webpath with adminFolderName on LegacyControllerContext
        $adminPath = $controller->admin_webpath ?? $controller->adminFolderName ?? '';
        $themeCss  = sprintf('%s%s/themes/new-theme/public/theme.css', __PS_BASE_URI__, $adminPath);
        $controller->addCSS($themeCss, 'all', 1);
    }

    /**
     * @param  \AdminController $controller
     * @param  string           $version
     *
     * @return void
     */
    /**
     * No type hint: PS 1.7/8 passes AdminController, PS 9 passes LegacyControllerContextProxy.
     * When dropping PS 1.7/8 support, type-hint to LegacyControllerContext instead.
     *
     * @param  \AdminController $controller
     * @param  string           $version
     *
     * @return void
     */
    protected function addVue($controller, string $version): void
    {
        $controller->addJS($this->getVueCdnUrl($version), false);
    }
}
