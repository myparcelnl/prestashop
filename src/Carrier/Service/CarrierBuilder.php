<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Carrier\Service;

use Carrier as PsCarrier;
use Context;
use Group;
use Language as PsLanguage;
use MyParcelNL;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Support\Str;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Exception\CreateCarrierException;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use RangePrice;
use RangeWeight;
use Zone as PsZone;

final class CarrierBuilder
{
    /**
     * @var array<class-string<\ObjectModel>>
     */
    private const RANGE_CLASSES = [RangePrice::class, RangeWeight::class];

    private PsCarrierMappingRepository    $carrierMappingRepository;

    private Carrier                       $myParcelCarrier;

    private PsCarrier                     $psCarrier;

    private PsCarrierServiceInterface     $psCarrierService;

    private PsObjectModelServiceInterface $psObjectModelService;

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $myParcelCarrier
     */
    public function __construct(Carrier $myParcelCarrier)
    {
        $this->myParcelCarrier          = $myParcelCarrier;
        $this->carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);
        $this->psObjectModelService     = Pdk::get(PsObjectModelServiceInterface::class);
        $this->psCarrierService         = Pdk::get(PsCarrierServiceInterface::class);
    }

    /**
     * @return \Carrier
     * @throws \Doctrine\ORM\ORMException
     * @throws \MyParcelNL\PrestaShop\Exception\CreateCarrierException
     */
    public function create(): PsCarrier
    {
        $this->createCarrier();

        $this->addCarrierImages();
        $this->addGroups();
        $this->addRanges();
        $this->addZones();

        $this->addCarrierMapping();

        return $this->psCarrier;
    }

    /**
     * @return void
     */
    private function addCarrierImages(): void
    {
        /** @var FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);
        $logosDir   = Pdk::get('carrierLogosDirectory');
        $logoName   = basename(FrontendData::getLegacyCarrierIdentifier($this->myParcelCarrier->carrier));

        foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
            $sourceFilename = $logosDir . $logoName . $fileExtension;

            if (! $fileSystem->fileExists($sourceFilename)) {
                continue;
            }

            try {
                $destFilename = _PS_SHIP_IMG_DIR_ . $this->psCarrier->id . $fileExtension;
                $fileSystem->put($destFilename, $fileSystem->get($sourceFilename));
            } catch (\Throwable $e) {
                Logger::warning("Failed to copy carrier logo: $sourceFilename", ['exception' => $e]);
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function addCarrierMapping(): void
    {
        $this->carrierMappingRepository->updateOrCreate(
            [
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => $this->myParcelCarrier->carrier,
            ],
            [
                MyparcelnlCarrierMapping::CARRIER_ID       => (int) $this->psCarrier->id,
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => $this->myParcelCarrier->carrier,
            ]
        );
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Exception\CreateCarrierException
     */
    private function addGroups(): void
    {
        $groups = Group::getGroups(Context::getContext()->language->id);

        if (! $this->psCarrier->setGroups(Arr::pluck($groups, 'id_group'))) {
            throw new CreateCarrierException("Failed to add groups to carrier $this->psCarrier->id");
        }
    }

    /**
     * @return void
     */
    private function addRanges(): void
    {
        foreach (self::RANGE_CLASSES as $objectClass) {
            $hasExistingRanges = $objectClass::getRanges($this->psCarrier->id);

            if ($hasExistingRanges) {
                continue;
            }

            /** @var RangeWeight|RangePrice $instance */
            $instance = $this->psObjectModelService->create($objectClass);

            $instance->id_carrier = $this->psCarrier->id;
            $instance->delimiter1 = '0';
            $instance->delimiter2 = '10000';

            $this->psObjectModelService->updateOrAdd($instance);
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Exception\CreateCarrierException
     */
    private function addZones(): void
    {
        $existingZones = new Collection($this->psCarrier->getZones());

        foreach (PsZone::getZones() as $zone) {
            $alreadyHasZone = $existingZones->contains(function (array $existingZone) use ($zone) {
                return $existingZone['id_zone'] === $zone['id_zone'];
            });

            if ($alreadyHasZone || $this->psCarrier->addZone($zone['id_zone'])) {
                continue;
            }

            throw new CreateCarrierException("Failed to add zone {$zone['id_zone']} to carrier {$this->psCarrier->id}");
        }
    }

    /**
     * @return void
     */
    private function createCarrier(): void
    {
        $psCarrier = $this->getExistingPsCarrier() ?? $this->psCarrierService->create();

        $carrierIdentifier = $this->myParcelCarrier->carrier;
        $translationKey    = 'carrier_' . Str::snake(Str::lower($carrierIdentifier));
        $translated        = Language::translate($translationKey);

        $psCarrier->name                 = $psCarrier->name
            ?? ($translated !== $translationKey ? $translated : $carrierIdentifier);

        $psCarrier->active               = $this->psCarrierService->carrierIsActive($this->myParcelCarrier);
        $psCarrier->deleted              = false;
        $psCarrier->external_module_name = MyParcelNL::MODULE_NAME;
        $psCarrier->is_module            = true;
        $psCarrier->need_range           = true;
        $psCarrier->range_behavior       = true;
        $psCarrier->shipping_external    = true;
        $psCarrier->shipping_method      = 2;

        $delay = $psCarrier->delay ?? [];
        foreach (PsLanguage::getLanguages() as $lang) {
            $existingString = $delay[$lang['id_lang']] ?? null;
            $newString      = Language::translate('carrier_delivery_time', $lang['iso_code']);

            $delay[$lang['id_lang']] = $existingString ?? $newString;
        }
        $psCarrier->delay = $delay;

        $this->psCarrierService->updateOrAdd($psCarrier);

        $this->psCarrier = $psCarrier;
    }

    /**
     * @return null|\Carrier
     */
    private function getExistingPsCarrier(): ?PsCarrier
    {
        $mapping = $this->carrierMappingRepository->findOneBy([
            MyparcelnlCarrierMapping::MYPARCEL_CARRIER => $this->myParcelCarrier->carrier,
        ]);

        if ($mapping) {
            return $this->psCarrierService->get($mapping->getCarrierId());
        }

        return null;
    }
}
