<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('creates address from ps address and ps order', function (AddressFactory $addressFactory, array $expectation) {
    $address = $addressFactory->store();
    $order   = psFactory(Order::class)
        ->withAddressDelivery($addressFactory)
        ->withCustomer($address->id_customer)
        ->store();

    /** @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter $adapter */
    $adapter = Pdk::get(PsAddressAdapter::class);

    $resultFromAddress = $adapter->fromAddress($address);
    $resultFromOrder   = $adapter->fromOrder($order);

    expect([
        'from address' => Utils::filterNull($resultFromAddress),
        'from order'   => Utils::filterNull($resultFromOrder),
    ])->toEqual([
        'from address' => $expectation,
        'from order'   => $expectation,
    ]);
})->with([
    'address' => [
        'addressFactory' => function () {
            $customer = psFactory(Customer::class)
                ->withFirstname('John')
                ->withLastname('Doe')
                ->withEmail('test@test.com');

            $country = psFactory(Country::class)
                ->withIsoCode('NL');

            return psFactory(Address::class)
                ->withCustomer($customer)
                ->withCountry($country)
                ->withFirstname('John')
                ->withLastname('Doe')
                ->withAddress1('Keizersgracht 1')
                ->withAddress2('A')
                ->withCity('Amsterdam')
                ->withPhone('0612345678')
                ->withPostcode('1015CC');
        },
        'expectation'    => [
            'cc'         => 'NL',
            'city'       => 'Amsterdam',
            'address1'   => 'Keizersgracht 1',
            'address2'   => 'A',
            'postalCode' => '1015CC',
            'person'     => 'John Doe',
            'phone'      => '0612345678',
        ],
    ],

]);
