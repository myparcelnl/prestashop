<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base;

use Module;
use MyParcelNL;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShopBundle\Exception\InvalidModuleException;
use ReflectionClass;
use ReflectionMethod;
use function DI\factory;
use function DI\value;

class PsPdkBootstrapper extends PdkBootstrapper
{
    protected const PRESTASHOP_REPOSITORIES = [
        'CarrierConfigurationRepository' => MyparcelnlCarrierMapping::class,
        'CartDeliveryOptionsRepository'  => MyparcelnlCartDeliveryOptions::class,
        'OrderDataRepository'            => MyparcelnlOrderData::class,
        'OrderShipmentRepository'        => MyparcelnlOrderShipment::class,
        'ProductSettingsRepository'      => MyparcelnlProductSettings::class,
    ];
    protected const PRESTASHOP_SERVICES     = [
        'ps.configuration' => 'prestashop.adapter.legacy.configuration',
        'ps.entityManager' => 'doctrine.orm.entity_manager',
        'ps.router'        => 'router',
        'ps.twig'          => 'twig',
    ];

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
        return array_merge([
            'userAgent' => value([
                'PrestaShop'          => _PS_VERSION_,
                'MyParcel-PrestaShop' => $version,
            ]),

            'triggerUpgradeBefore' => value('2.0.1'),
            'moduleTabName'        => value('shipping_logistics'),

            'prestaShopVersionMin' => value('1.7.6'),
            'prestaShopVersionMax' => value('8.0'),

            'moduleInstance' => factory(static function () use ($name): MyParcelNL {
                /** @var MyParcelNL|false $module */
                $module = Module::getInstanceByName($name);

                if (! $module) {
                    throw new InvalidModuleException('Failed to get module instance');
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
                    });

                return $hooks->toArray();
            }),
        ],
            $this->resolvePrestaShopRepositories(),
            $this->resolvePrestaShopServices()
        );
    }

    /**
     * Resolve entity manager repositories for our added entities, so we can use them intuitively.
     *
     * @return array
     */
    private function resolvePrestaShopRepositories(): array
    {
        return array_map(static function ($repositoryName) {
            return factory(function () use ($repositoryName) {
                /** @var \PrestaShop\PrestaShop\Core\Foundation\Database\EntityManager $entityManager */
                $entityManager = Pdk::get('ps.entityManager');

                return $entityManager->getRepository($repositoryName);
            });
        }, self::PRESTASHOP_REPOSITORIES);
    }

    /**
     * @return array|\DI\Definition\Helper\FactoryDefinitionHelper[]
     * @throws \PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException
     */
    private function resolvePrestaShopServices(): array
    {
        return array_map(static function ($serviceName) {
            return factory(function (MyParcelNL $module) use ($serviceName) {
                return $module->getContainer()
                    ->get($serviceName);
            });
        }, self::PRESTASHOP_SERVICES);
    }
}
