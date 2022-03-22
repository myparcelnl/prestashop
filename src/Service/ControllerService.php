<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class ControllerService
{
    public const LABEL           = 'myparcelbe_label';
    public const LOADING         = 'myparcelbe_loading';
    public const BUTTON_ACTION   = 'myparcelbe_button_action';
    public const MODULE_SETTINGS = 'myparcelbe_module_settings';
    public const ORDER           = 'myparcelbe_order';

    /**
     * Fixes route urls for sites that are hosted in a subfolder instead of the root. In the frontend, we create urls
     * to routers like this: <adminUrl> + <path>. In the case of a site hosted at the root, the parts are "site.com/"
     * and "<adminFolder>/path/to/controller". This will work when concatenated, but when the site is in a folder we
     * get this: "site.com/<subfolder>/" + "<subfolder>/<adminFolder>/path/to/controller". A more robust fix would
     * be to use absolute urls, but PrestaShop made it impossible in their router to generate these. So now we just
     * remove one of the subfolder paths.
     *
     * @param string $baseUrl
     * @param string $route
     *
     * @return string
     */
    public static function createActionPath(string $baseUrl, string $route): string
    {
        $baseUrlParts = parse_url($baseUrl);
        $routePath    = self::generateUri($route);

        return str_replace($baseUrlParts['path'], '/', $routePath);
    }

    /**
     * @param string $route
     *
     * @return null|string
     */
    public static function generateUri(string $route): ?string
    {
        $container = SymfonyContainer::getInstance();
        if (! $container) {
            return null;
        }

        $router = $container->get('router');
        if (! $router) {
            return null;
        }

        return $router->generate($route);
    }
}
