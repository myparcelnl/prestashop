<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base;

use MyParcelNL;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use function DI\factory;
use function DI\value;

class PsPdkBootstrapper extends PdkBootstrapper
{
    protected const PRESTASHOP_REPOSITORIES = [
        'CarrierConfigurationRepository' => MyparcelnlCarrierConfiguration::class,
        'CartDeliveryOptionsRepository'  => MyparcelnlCartDeliveryOptions::class,
        'OrderDataRepository'            => MyparcelnlOrderData::class,
        'OrderShipmentRepository'        => MyparcelnlOrderShipment::class,
        'ProductSettingsRepository'      => MyparcelnlProductSettings::class,
    ];
    protected const PRESTASHOP_SERVICES     = [
        'ps.entityManager' => 'doctrine.orm.entity_manager',
        'ps.configuration' => 'prestashop.adapter.legacy.configuration',
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

            'triggerUpgradeBefore' => '2.0.1',
            'moduleTabName'        => value('shipping_logistics'),

            'prestaShopVersionMin' => '1.7.6',
            'prestaShopVersionMax' => '8.0',
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
