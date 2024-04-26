<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollectionFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Model\SettingsFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\EntityInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Tests\Factory\PsFactoryFactory;
use ObjectModel;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @param  class-string<ObjectModel|EntityInterface> $class
 * @param  mixed                                     ...$args
 *
 * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
 */
function psFactory(string $class, ...$args)
{
    return PsFactoryFactory::create($class, ...$args);
}

function setupAccountAndCarriers(CarrierCollectionFactory $factory): Collection
{
    TestBootstrapper::hasAccount(
        TestBootstrapper::API_KEY_VALID,
        factory(ShopCollection::class)->push(factory(Shop::class)->withCarriers($factory))
    );

    return new Collection($factory->make());
}

function setupCarrierActiveSettings(array $settings): SettingsFactory
{
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)
                ->fromPostNL()
                ->withEnabled(true)
        )
    );

    (new FactoryCollection([
        psFactory(PsCarrier::class)
            ->withId(12),

        psFactory(MyparcelnlCarrierMapping::class)
            ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_NAME)
            ->withCarrierId(12),
    ]))->store();

    return factory(Settings::class)
        ->withCheckout([
            CheckoutSettings::ENABLE_DELIVERY_OPTIONS => $settings[CheckoutSettings::ENABLE_DELIVERY_OPTIONS] ?? false,
        ])
        ->withCarrierPostNl([
            CarrierSettings::DELIVERY_OPTIONS_ENABLED => $settings[CarrierSettings::DELIVERY_OPTIONS_ENABLED] ?? false,
            CarrierSettings::ALLOW_DELIVERY_OPTIONS   => $settings[CarrierSettings::ALLOW_DELIVERY_OPTIONS] ?? false,
            CarrierSettings::ALLOW_PICKUP_LOCATIONS   => $settings[CarrierSettings::ALLOW_PICKUP_LOCATIONS] ?? false,
        ]);
}
