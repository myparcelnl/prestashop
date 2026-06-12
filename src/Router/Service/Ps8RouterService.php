<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Service;

use Context;
use Link;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

final class Ps8RouterService extends PsRouterService
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    private Router $router;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);
        $this->router = $this->getRouter();
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    protected function generateRoute(string $route): string
    {
        // The frontend endpoint is a module front controller, not a Symfony route.
        // Link::getModuleLink() is the only reliable way to resolve it on any PS
        // installation regardless of URL-rewriting or nginx configuration.
        if (Pdk::get('routeNameFrontend') === $route) {
            /** @var Link $link */
            $link = Context::getContext()->link;

            return $link->getModuleLink('myparcelnl', 'frontend');
        }

        return $this->router->generate($route);
    }

    /**
     * @return \Symfony\Component\Routing\Router
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getRouter(): Router
    {
        /** @var \Psr\Container\ContainerInterface $container */
        $container = Pdk::get('ps.container');

        if ($this->isCheckoutPage()) {
            $routesDirectory = _PS_ROOT_DIR_ . '/modules/myparcelnl/config';
            $locator         = new FileLocator([$routesDirectory]);
            $loader          = new YamlFileLoader($locator);

            return new Router($loader, 'routes.yml');
        }

        return $container->get('prestashop.router');
    }

    /**
     * @return bool
     */
    private function isCheckoutPage(): bool
    {
        return 'order' === (Context::getContext()->controller->php_self ?? null);
    }
}
