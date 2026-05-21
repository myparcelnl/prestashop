<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Service;

use Context;
use Link;
use MyParcelNL\Pdk\Facade\Pdk;

final class Ps17RouterService extends PsRouterService
{
    /**
     * @param  string $route
     *
     * @return string
     */
    protected function generateRoute(string $route): string
    {
        /** @var Link $link */
        $link = Context::getContext()->link;

        if (Pdk::get('routeNameFrontend') === $route) {
            return $link->getModuleLink('myparcelnl', 'frontend');
        }

        if (! defined('_PS_ADMIN_DIR_')) {
            return '';
        }

        return $link->getAdminLink('MyParcelNLAdminSettings', true, ['route' => $route]);
    }
}
