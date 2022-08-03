<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use function DI\factory;
use function DI\value;

class Boot
{
    protected const PRESTASHOP_SERVICES = [
        'ps.entityManager' => 'doctrine.orm.entity_manager',
        'ps.configuration' => 'prestashop.adapter.legacy.configuration',
        'ps.twig'          => 'twig',
    ];

    protected const PRESTASHOP_REPOSITORIES = [
        'CarrierConfigurationRepository' => MyparcelnlCarrierConfiguration::class,
        'CartDeliveryOptionsRepository'  => MyparcelnlCartDeliveryOptions::class,
        'OrderDataRepository'            => MyparcelnlOrderData::class,
        'OrderShipmentRepository'        => MyparcelnlOrderShipment::class,
        'ProductSettingsRepository'      => MyparcelnlProductSettings::class,
    ];

    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var \MyParcelNL\Pdk\Base\Pdk
     */
    private static $pdk;

    /**
     * @return void
     * @throws \Throwable
     */
    public static function setupPdk(\MyParcelNL $module): ?Pdk
    {
        if (! self::$initialized) {
            self::$initialized = true;
            self::$pdk         = PdkFactory::create(
                sprintf('%sconfig/pdk.php', $module->getLocalPath()),

                [
                    'appInfo' => value([
                        'name'    => MyParcelNL::MODULE_NAME,
                        'title'   => 'MyParcel',
                        'path'    => $module->getLocalPath(),
                        'url'     => $module->getBaseUrl(),
                        'version' => $module->version,
                    ]),

                    'platform' => MyParcelNL::MODULE_NAME === 'myparcelnl' ? Platform::MYPARCEL_NAME
                        : Platform::SENDMYPARCEL_NAME,

                    'userAgent' => value([
                        'MyParcel-PrestaShop' => $module->version,
                        'PrestaShop'          => _PS_VERSION_,
                    ]),

                ],

                self::resolvePrestaShopServices(),
                self::resolvePrestaShopRepositories()
            );
        }

        return self::$pdk;
    }

    /**
     * Resolve entity manager repositories for our added entities, so we can use them intuitively.
     *
     * @return array
     */
    private static function resolvePrestaShopRepositories(): array
    {
        return array_map(static function ($serviceName) {
            return factory(function () use ($serviceName) {
                /** @var \PrestaShop\PrestaShop\Core\Foundation\Database\EntityManager $entityManager */
                $entityManager = \MyParcelNL\Pdk\Facade\Pdk::get('ps.entityManager');

                return $entityManager->getRepository($serviceName);
            });
        }, self::PRESTASHOP_REPOSITORIES);
    }

    /**
     * @return array|\DI\Definition\Helper\FactoryDefinitionHelper[]
     * @throws \PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException
     */
    private static function resolvePrestaShopServices(): array
    {
        return array_map(static function ($serviceName) {
            return factory(function (\MyParcelNL $module) use ($serviceName) {
                return $module->getContainer()
                    ->get($serviceName);
            });
        }, self::PRESTASHOP_SERVICES);
    }
}
