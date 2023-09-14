<?php

/** @noinspection AutoloadingIssuesInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,PhpUnused,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Carrier as PsCarrier;
use Exception;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

class WithHasPsCarrierHooks
{
    use HasPsCarrierHooks;
}

it('re-synchronises carrier id when it is updated', function () {
    /** @var PsCarrierMappingRepository $carrierMappingRepository */
    $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);

    psFactory(PsCarrier::class)
        ->withId(14)
        ->store();

    psFactory(MyparcelnlCarrierMapping::class)
        ->withCarrierId(14)
        ->withMyparcelCarrier(Carrier::CARRIER_DPD_NAME)
        ->store();

    $class = new WithHasPsCarrierHooks();

    $class->hookActionCarrierUpdate([
        'id_carrier' => 14,
        'carrier'    => (object) ['id' => 15],
    ]);

    /** @var MyparcelnlCarrierMapping $found */
    $found = $carrierMappingRepository->findOneBy(['myparcelCarrier' => Carrier::CARRIER_DPD_NAME]);

    if (null === $found) {
        throw new Exception('No match found');
    }

    expect($found->getCarrierId())->toBe(15);
});
