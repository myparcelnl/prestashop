<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base;

use Context;
use DI\Definition\Helper\FactoryDefinitionHelper;
use Module;
use MyParcelNL;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException;
use PrestaShopBundle\Exception\InvalidModuleException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;
use function DI\factory;
use function DI\value;

class PsPdkBootstrapper extends PdkBootstrapper
{
    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return array
     * @throws \PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException
     */
    protected function getAdditionalConfig(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): array {
        return array_replace(
            $this->getConfig($version, $name),
            $this->resolvePrestaShopServices()
        );
    }

    /**
     * @param  string $version
     * @param  string $name
     *
     * @return array
     */
    protected function getConfig(string $version, string $name): array
    {
        return [
            'userAgent' => value([
                'PrestaShop'          => _PS_VERSION_,
                'MyParcel-PrestaShop' => $version,
            ]),

            'prestaShopVersionMin' => value('1.7.6'),
            'prestaShopVersionMax' => value('8.2.0'),

            /**
             * Tab in the modules list we want to show the module under.
             */

            'moduleTabName' => value('shipping_logistics'),

            /**
             * The name of the tab we want to show the settings page under.
             */

            'sidebarParentClass' => value('AdminParentShipping'),

            'logDirectory' => value(sprintf('%s/var/logs/%s', _PS_ROOT_DIR_, $name)),

            /**
             * The symfony routes that are used by the pdk. Must match the routes in config/routes.yml.
             *
             * @see config/routes.yml
             */

            'routeNameFrontend' => value('myparcelnl_frontend'),
            'routeNamePdk'      => value('myparcelnl_pdk'),
            'routeNameSettings' => value('myparcelnl_settings'),
            'routeNameWebhook'  => value('myparcelnl_webhook'),

            'legacyControllerSettings' => value('MyParcelNLAdminSettings'),

            'moduleInstance' => factory(static function (): Module {
                $name = Pdk::getAppInfo()->name;

                /** @var MyParcelNL|false $module */
                $module = Module::getInstanceByName($name);

                if (! $module) {
                    throw new InvalidModuleException("Failed to get module instance '$name'");
                }

                return $module;
            }),

            /**
             * Get all hooks from the MyParcelNL class dynamically.
             */
            'moduleHooks'    => factory(function () {
                $reflectionClass = new ReflectionClass(MyParcelNL::class);

                $hooks = (new Collection($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC)))
                    ->filter(function (ReflectionMethod $method) {
                        return Str::startsWith($method->getName(), 'hook');
                    })
                    ->map(function (ReflectionMethod $method) {
                        return lcfirst(preg_replace('/^hook/', '', $method->getName()));
                    })
                    ->values();

                return $hooks->toArray();
            }),
        ];
    }

    /**
     * @return array|FactoryDefinitionHelper[]
     * @throws ContainerNotFoundException
     */
    private function resolvePrestaShopServices(): array
    {
        return [
            'getPsService' => factory(function () {
                return static function (string $serviceName) {
                    /** @var MyParcelNL $module */
                    $module = Pdk::get('moduleInstance');

                    return $module
                        ->getContainer()
                        ->get($serviceName);
                };
            }),

            'ps.configuration' => factory(function () {
                return Pdk::get('getPsService')('prestashop.adapter.legacy.configuration');
            }),

            'ps.entityManager' => factory(function () {
                return Pdk::get('getPsService')('doctrine.orm.entity_manager');
            }),

            'ps.tabRepository' => factory(function () {
                return Pdk::get('getPsService')('prestashop.core.admin.tab.repository');
            }),

            'ps.router' => factory(function () {
                /** @var MyParcelNL $module */
                $module    = Pdk::get('moduleInstance');
                $container = $module->getContainer();

                if (_PS_VERSION_ <= 8) {
                    return $container->get('router');
                }

                $controller = Context::getContext()->controller;

                if ('order' === $controller->php_self) {
                    $routesDirectory = _PS_ROOT_DIR_ . '/modules/myparcelnl/config';
                    $locator         = new FileLocator([$routesDirectory]);
                    $loader          = new YamlFileLoader($locator);

                    return new Router($loader, 'routes.yml');
                }

                return $container->get('prestashop.router');
            }),

            'ps.twig' => factory(function () {
                return Pdk::get('getPsService')('twig');
            }),

        ];
    }
}
