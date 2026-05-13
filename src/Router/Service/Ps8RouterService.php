<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Service;

use Context;
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
     * On the checkout (order) page the Symfony container isn't available, so we
     * build a standalone Router from routes.yml. That router has no knowledge of
     * the /modules/ prefix PrestaShop applies when it imports module routes, so
     * we track it and prepend the prefix ourselves.
     *
     * @var bool
     */
    private bool $useModulePrefix = false;

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
        $url = $this->router->generate($route);

        return $this->useModulePrefix ? '/modules' . $url : $url;
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

        $controller = Context::getContext()->controller;

        if ('order' === $controller->php_self) {
            $routesDirectory       = _PS_ROOT_DIR_ . '/modules/myparcelnl/config';
            $locator               = new FileLocator([$routesDirectory]);
            $loader                = new YamlFileLoader($locator);
            $this->useModulePrefix = true;

            return new Router($loader, 'routes.yml');
        }

        return $container->get('prestashop.router');
    }
}
