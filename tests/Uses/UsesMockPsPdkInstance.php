<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Uses;

use Address;
use CarrierModule;
use Configuration;
use Country;
use Customer;
use Gender;
use Group;
use Lang;
use Manufacturer;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Tests\Factory\SharedFactoryState;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use OrderState;
use OrderStateFactory;
use Risk;
use Shop;
use ShopGroup;
use State;
use Supplier;
use Warehouse;
use Zone;
use function MyParcelNL\PrestaShop\psFactory;

class UsesMockPsPdkInstance extends UsesEachMockPdkInstance
{
    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public function createZones(): void
    {
        (new FactoryCollection([
            psFactory(Zone::class)
                ->withName('Europe'),
            psFactory(Zone::class)
                ->withName('North America'),
        ]))->store();
    }

    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function addDefaultData(): void
    {
        psFactory(Configuration::class)->make();

        (new FactoryCollection([
            psFactory(Country::class)
                ->withIsoCode('NL'),
            psFactory(Address::class),
            psFactory(Address::class)->withAddress2('(Billing)'),
            psFactory(Customer::class),
            psFactory(Gender::class),
            psFactory(Group::class),
            psFactory(Lang::class),
            psFactory(Manufacturer::class),
            psFactory(Risk::class),
            psFactory(Shop::class),
            psFactory(ShopGroup::class),
            psFactory(State::class),
            psFactory(Supplier::class),
            psFactory(Warehouse::class),
        ]))->store();

        $this->createZones();
        $this->createOrderStates();

        /** @var FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);

        $fileSystem->mkdir(_PS_SHIP_IMG_DIR_, true);
        $fileSystem->mkdir(Pdk::get('carrierLogosDirectory'), true);

        /** @var FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);

        foreach (Config::get('carriers') as $carrier) {
            foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
                $filename = Pdk::get('carrierLogosDirectory') . $carrier['name'] . $fileExtension;

                $fileSystem->put($filename, '[IMAGE]');
            }
        }
    }

    protected function reset(): void
    {
        if (Facade::getPdkInstance()) {
            Pdk::get(SharedFactoryState::class)
                ->reset();
        }

        parent::reset();
    }

    protected function setup(): void
    {
        MockPsPdkBootstrapper::setConfig($this->config);
        MockPsPdkBootstrapper::boot('pest', 'Pest', '1.0.0', __DIR__ . '/../../', 'APP_URL');
        MockPsModule::setInstance('pest', new CarrierModule());

        $this->addDefaultData();
    }

    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    private function createOrderStates(): void
    {
        $collection = new FactoryCollection([
            psFactory(OrderState::class)
                ->withId(1)
                ->withName('Awaiting check payment'),
            psFactory(OrderState::class)
                ->withId(2)
                ->withName('Payment accepted')
                ->withPaid(1),
            psFactory(OrderState::class)
                ->withId(3)
                ->withName('Processing in progress')
                ->withDelivery(1)
                ->withPaid(1),
            psFactory(OrderState::class)
                ->withId(4)
                ->withName('Shipped')
                ->withDelivery(1)
                ->withPaid(1)
                ->withShipped(1),
            psFactory(OrderState::class)
                ->withId(5)
                ->withName('Delivered')
                ->withPaid(1)
                ->withDelivery(1)
                ->withShipped(1),
            psFactory(OrderState::class)
                ->withId(6)
                ->withName('Canceled'),
            psFactory(OrderState::class)
                ->withId(7)
                ->withName('Refunded'),
            psFactory(OrderState::class)
                ->withId(8)
                ->withName('Payment error'),
            psFactory(OrderState::class)
                ->withId(9)
                ->withName('On backorder (paid)')
                ->withPaid(1),
            psFactory(OrderState::class)
                ->withId(10)
                ->withName('Awaiting bank wire payment'),
            psFactory(OrderState::class)
                ->withId(11)
                ->withName('Remote payment accepted')
                ->withPaid(1),
            psFactory(OrderState::class)
                ->withId(12)
                ->withName('On backorder (not paid)'),
            psFactory(OrderState::class)
                ->withId(13)
                ->withName('Awaiting Cash On Delivery validation'),
        ]);

        $collection
            ->map(function (OrderStateFactory $factory) {
                return $factory->withUnremovable(1);
            })
            ->store();
    }
}
