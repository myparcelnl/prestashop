<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Service;

use Context;

final class Ps17RouterService extends PsRouterService
{
    /**
     * @param  string $route
     *
     * @return string
     */
    protected function generateRoute(string $route): string
    {
        /** @var \LinkCore $link */
        $link = Context::getContext()->link;

        if (! defined('_PS_ADMIN_DIR_')) {
            // Do not generate admin links in the frontend. There are currently no frontend endpoints anyway.
            return '';
        }

        return $link->getAdminLink('MyParcelNLAdminSettings', true, ['route' => $route]);
    }
}
