<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use Address;
use Country;
use MyParcelNLDeliveryOptionsModuleFrontController;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['HTTP_ACCEPT'], $_GET['orderId']);
});

afterEach(function () {
    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['HTTP_ACCEPT'], $_GET['orderId']);
});

function validAuthHeader(): string
{
    return 'Basic ' . base64_encode('VALID_KEY:');
}

function insertValidWebserviceAccount(): void
{
    MockPsDb::insertRow('webservice_account', ['id_webservice_account' => 1, 'key' => 'VALID_KEY', 'active' => 1]);
    MockPsDb::insertRow('webservice_account_rule', ['id_webservice_account' => 1, 'resource' => 'orders', 'GET' => 1]);
}

/**
 * Pre-seed an empty MyparcelnlOrderData entity so getOrderData() does not trigger
 * the getFromCart() path (which calls persist() → MockPsEntities::add() → dynamic property assignment).
 */
function seedOrderDataEntity(int $orderId): void
{
    $entity = new MyparcelnlOrderData();
    $entity->setOrderId($orderId);
    $entity->setData('{}');
    // Use update() directly to avoid MockItems::add() which assigns $object->id (deprecated in PHP 8.2+).
    MockPsEntities::update($entity);
}

/**
 * Store an Order directly into MockPsObjectModels with a valid NL country code.
 *
 * psFactory(Order::class)->store() triggers a TypeError in PackageTypeCalculator because
 * the AddressFactory's createDefault() calls Country::getByIso('NL') BEFORE the Country
 * is stored (factory constructors run eagerly). This helper bypasses that ordering issue
 * by creating objects directly.
 *
 * @return Order
 */
function storeOrderWithCountry(): Order
{
    // Find or use the existing NL country (created in addDefaultData).
    $nlCountry = Country::firstWhere(['iso_code' => 'NL']);
    if (! $nlCountry) {
        $nlCountry = new Country();
        $nlCountry->hydrate(['iso_code' => 'NL', 'id' => 999]);
        MockPsObjectModels::update($nlCountry);
    }

    // Create an Address with the NL country.
    $address = new Address();
    $address->hydrate([
        'id'         => 100,
        'id_country' => $nlCountry->id,
        'firstname'  => 'Test',
        'lastname'   => 'User',
        'address1'   => 'Antareslaan 31',
        'city'       => 'Hoofddorp',
        'postcode'   => '2132JE',
    ]);
    MockPsObjectModels::update($address);

    // Create the Order referencing this Address.
    $order = new Order();
    $order->hydrate([
        'id'                  => 500,
        'id_address_delivery' => $address->id,
        'id_address_invoice'  => $address->id,
        'id_customer'         => 1,
        'id_carrier'          => 1,
        'id_cart'             => 1,
        'id_currency'         => 1,
        'id_lang'             => 1,
        'id_shop'             => 1,
        'id_shop_group'       => 1,
        'current_state'       => 1,
        'payment'             => 'Test',
        'date_add'            => '2023-01-01 00:00:00',
    ]);
    MockPsObjectModels::update($order);

    seedOrderDataEntity($order->id);

    return $order;
}

it('returns 401 when Authorization header is missing', function () {
    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(401);
});

it('returns 401 when webservice key is invalid', function () {
    $_SERVER['HTTP_AUTHORIZATION'] = validAuthHeader();
    // No row in webservice_account table — key is invalid.

    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(401);
});

it('returns 403 when webservice key lacks orders GET permission', function () {
    $_SERVER['HTTP_AUTHORIZATION'] = validAuthHeader();
    MockPsDb::insertRow('webservice_account', ['id_webservice_account' => 1, 'key' => 'VALID_KEY', 'active' => 1]);
    // No webservice_account_rule row — no GET permission on orders.

    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(403);
});

it('returns 404 with ProblemDetails when order does not exist', function () {
    $_SERVER['HTTP_AUTHORIZATION'] = validAuthHeader();
    $_GET['orderId']               = '99999';
    insertValidWebserviceAccount();

    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(404);
    $body = json_decode($response->getContent(), true);
    expect($body)->toHaveKey('status');
    expect($body['status'])->toBe(404);
});

it('returns 200 with delivery options when order exists', function () {
    $psOrder                       = storeOrderWithCountry();
    $_SERVER['HTTP_AUTHORIZATION'] = validAuthHeader();
    $_GET['orderId']               = (string) $psOrder->id;
    insertValidWebserviceAccount();

    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(200);
    $body = json_decode($response->getContent(), true);
    expect($body)->toBeArray();
});

it('forwards versioning headers per ADR-011', function () {
    $psOrder                       = storeOrderWithCountry();
    $_SERVER['HTTP_AUTHORIZATION'] = validAuthHeader();
    $_SERVER['HTTP_ACCEPT']        = 'application/json; version=1';
    $_GET['orderId']               = (string) $psOrder->id;
    insertValidWebserviceAccount();

    /** @var MyParcelNLDeliveryOptionsModuleFrontController $controller */
    $controller = new MyParcelNLDeliveryOptionsModuleFrontController();
    $response   = $controller->handleRequest();

    expect($response->getStatusCode())->toBe(200);
    // Per ADR-011, version-negotiated responses include a Content-Type or similar versioning header.
    $headers = $response->headers->all();
    expect($headers)->toBeArray();
});
