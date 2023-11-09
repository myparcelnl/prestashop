<?php

/** @noinspection AutoloadingIssuesInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,PhpUnused,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Carrier as PsCarrier;
use Exception;
use MyParcelNL\Pdk\Base\FileSystemInterface;
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

it('re-synchronises carrier id and logo when it is updated', function () {
    /** @var PsCarrierMappingRepository $carrierMappingRepository */
    $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    psFactory(PsCarrier::class)
        ->withId(14)
        ->store();

    psFactory(MyparcelnlCarrierMapping::class)
        ->withCarrierId(14)
        ->withMyparcelCarrier(Carrier::CARRIER_DPD_NAME)
        ->store();

    foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
        $fileSystem->put(_PS_SHIP_IMG_DIR_ . '14' . $fileExtension, '[IMAGE]');
    }

    $class = new WithHasPsCarrierHooks();

    $class->hookActionCarrierUpdate([
        'id_carrier' => 14,
        'carrier'    => (object) ['id' => 15],
    ]);

    /** @var MyparcelnlCarrierMapping $found */
    $found = $carrierMappingRepository->findOneBy([
        MyparcelnlCarrierMapping::MYPARCEL_CARRIER => Carrier::CARRIER_DPD_NAME,
    ]);

    if (null === $found) {
        throw new Exception('No match found');
    }

    expect($found->getCarrierId())->toBe(15);

    foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
        expect($fileSystem->fileExists(_PS_SHIP_IMG_DIR_ . '14' . $fileExtension))
            ->toBeFalse()
            ->and($fileSystem->fileExists(_PS_SHIP_IMG_DIR_ . '15' . $fileExtension))
            ->toBeTrue();
    }
});
