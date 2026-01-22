<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Carrier as PsCarrier;
use Cart;
use CartFactory;
use Context;
use FrontController;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsTools;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

final class ClassWithTrait
{
    use HasPsShippingCostHooks;

    protected $context;

    protected $hasPdk;

    public function __construct(bool $hasPdk = true)
    {
        $this->context             = Context::getContext();
        $this->context->controller = new FrontController();
        $this->hasPdk              = $hasPdk;
        $this->cachedPrices        = [];
    }

    public function setIdCarrier(int $idCarrier): void
    {
        $this->id_carrier = $idCarrier;
    }

    public function setCart(Cart $cart): void
    {
        $this->context->cart = $cart;
    }
}

usesShared(new UsesMockPsPdkInstance());

it('calculates shipping costs', function (CartFactory $cartFactory, array $deliveryOptions = [], float $addedCost = 0) {
    if ($deliveryOptions) {
        MockPsTools::setValues([
            Pdk::get('checkoutHiddenInputName') => json_encode($deliveryOptions),
        ]);
    }

    $instance = new ClassWithTrait();
    $instance->setIdCarrier(93);

    $baseCost = 10;
    $cost     = $instance->getOrderShippingCost($cartFactory->make(), $baseCost);

    expect(number_format($cost, 2))->toEqual(number_format($baseCost + $addedCost, 2));
})->with([
    'no carrier' => [
        function () {
            return psFactory(Cart::class);
        },
    ],

    'carrier without linked myparcel carrier' => [
        function () {
            $psCarrier = psFactory(PsCarrier::class)
                ->withId(93)
                ->store();

            return psFactory(Cart::class)->withCarrier($psCarrier);
        },
    ],

    'carrier with linked myparcel carrier but no delivery options' => [
        function () {
            $psCarrier = psFactory(PsCarrier::class)->withId(93);

            (new FactoryCollection([
                $psCarrier,
                psFactory(MyparcelnlCarrierMapping::class)
                    ->withCarrierId(93)
                    ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_NAME),
                factory(Settings::class)->withCarrierPostNl([
                    CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD => 2.95,
                ]),
            ]))->store();

            return psFactory(Cart::class)->withCarrier($psCarrier);
        },
        [],
        'cost' => 2.95,
    ],

    'carrier with linked myparcel carrier and delivery options in values' => [
        function () {
            $psCarrier = psFactory(PsCarrier::class)->withId(93);

            (new FactoryCollection([
                $psCarrier,
                psFactory(MyparcelnlCarrierMapping::class)
                    ->withCarrierId(93)
                    ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_LEGACY_NAME),
                factory(Settings::class)
                    ->withCarrierPostNl([
                        CarrierSettings::PRICE_SIGNATURE              => 0.45,
                        CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD => 4.95,
                    ]),
            ]))->store();

            return psFactory(Cart::class)->withCarrier($psCarrier);
        },
        'values' => [
            DeliveryOptions::CARRIER          => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            DeliveryOptions::DELIVERY_TYPE    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::SHIPMENT_OPTIONS => [
                ShipmentOptions::SIGNATURE => true,
            ],
        ],
        'cost'   => 5.4,
    ],
]);

it('returns input shipping cost if pdk is not set up', function () {
    $instance = new ClassWithTrait(false);

    $result = $instance->getOrderShippingCost(psFactory(Cart::class)->make(), 123.45);

    expect($result)->toBe(123.45);
});

it(
    'calculates shipping costs using database',
    function (CartFactory $cartFactory, array $deliveryOptions = [], float $addedCost = 0) {
        $cart = $cartFactory->make();

        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        $deliveryOptions = new DeliveryOptions($deliveryOptions);

        $cartDeliveryOptionsRepository->updateOrCreate(
            [
                'cartId' => $cart->id,
            ],
            [
                'data' => json_encode($deliveryOptions->toStorableArray()),
            ]
        );

        $instance = new ClassWithTrait();
        $instance->setIdCarrier(93);
        $instance->setCart($cart);

        $baseCost = 10;
        $cost     = $instance->getOrderShippingCost($cart, $baseCost);

        expect(number_format($cost, 2))->toEqual(number_format($baseCost + $addedCost, 2));
    }
)->with([
    'standard delivery with delivery options in values' => [
        function () {
            $psCarrier = psFactory(PsCarrier::class)->withId(93);

            (new FactoryCollection([
                $psCarrier,
                psFactory(MyparcelnlCarrierMapping::class)
                    ->withCarrierId(93)
                    ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_NAME),
                factory(Settings::class)->withCarrierPostNl([
                    CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD => 2.95,
                ]),
            ]))->store();

            return psFactory(Cart::class)->withCarrier($psCarrier);
        },
        'values' => [
            DeliveryOptions::CARRIER       => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        ],
        'cost'   => 2.95,
    ],

    'carrier with linked myparcel carrier and delivery options in values' => [
        function () {
            $psCarrier = psFactory(PsCarrier::class)->withId(93);

            (new FactoryCollection([
                $psCarrier,
                psFactory(MyparcelnlCarrierMapping::class)
                    ->withCarrierId(93)
                    ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_LEGACY_NAME),
                factory(Settings::class)
                    ->withCarrierPostNl([
                        CarrierSettings::PRICE_SIGNATURE              => 0.45,
                        CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD => 4.95,
                    ]),
            ]))->store();

            return psFactory(Cart::class)->withCarrier($psCarrier);
        },
        'values' => [
            DeliveryOptions::CARRIER          => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            DeliveryOptions::DELIVERY_TYPE    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::SHIPMENT_OPTIONS => [
                ShipmentOptions::SIGNATURE => true,
            ],
        ],
        'cost'   => 5.4,
    ],
]);
