<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Carrier\Service;

use Carrier as PsCarrier;
use Context;
use Group;
use Language as PsLanguage;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use RangePrice;
use RangeWeight;
use RuntimeException;
use Zone;

final class CarrierBuilder
{
    private const RANGE_CLASSES = [RangePrice::class, RangeWeight::class];

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository
     */
    private $carrierMappingRepository;

    /**
     * @var \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    private $myParcelCarrier;

    /**
     * @var \Carrier
     */
    private $psCarrier;

    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface
     */
    private $psCarrierService;

    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface
     */
    private $psObjectModelService;

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

        foreach (Pdk::get('carrierLogoFileExtensions') as $fileExtension) {
            $sourceFilename = Pdk::get('carrierLogosDirectory') . $this->myParcelCarrier->name . $fileExtension;
            $destFilename   = _PS_SHIP_IMG_DIR_ . $this->psCarrier->id . $fileExtension;

            $fileSystem->put($destFilename, $fileSystem->get($sourceFilename));
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function addCarrierMapping(): void
    {
        $values = [
            MyparcelnlCarrierMapping::CARRIER_ID       => (int) $this->psCarrier->id,
            MyparcelnlCarrierMapping::MYPARCEL_CARRIER => $this->myParcelCarrier->externalIdentifier,
        ];

        $this->carrierMappingRepository->updateOrCreate($values, $values);
    }

    /**
     * @return void
     */
    private function addGroups(): void
    {
        $groups = Group::getGroups(Context::getContext()->language->id);

        if (! $this->psCarrier->setGroups(Arr::pluck($groups, 'id_group'))) {
            throw new RuntimeException("Failed to add groups to carrier $this->psCarrier->id");
        }
    }

    /**
     * @return void
     */
    private function addRanges(): void
    {
        /** @var RangeWeight|RangePrice $objectClass */
        foreach (self::RANGE_CLASSES as $objectClass) {
            $hasExistingRanges = $objectClass::getRanges($this->psCarrier->id);

            if ($hasExistingRanges) {
                continue;
            }

            $instance = $this->psObjectModelService->create($objectClass);

            $instance->id_carrier = $this->psCarrier->id;
            $instance->delimiter1 = '0';
            $instance->delimiter2 = '10000';

            $this->psObjectModelService->updateOrAdd($instance);
        }
    }

    /**
     * @return void
     */
    private function addZones(): void
    {
        $existingZones = $this->psCarrier->getZones();

        if ($existingZones) {
            return;
        }

        foreach (Zone::getZones() as $zone) {
            if ($this->psCarrier->addZone($zone['id_zone'])) {
                continue;
            }

            throw new RuntimeException("Failed to add zone {$zone['id_zone']} to carrier {$this->psCarrier->id}");
        }
    }

    /**
     * @return void
     */
    private function createCarrier(): void
    {
        /** @var \MyParcelNL $module */
        $module = Pdk::get('moduleInstance');

        $psCarrier = $this->getExistingPsCarrier() ?? $this->psCarrierService->create();

        $psCarrier->name                 = $psCarrier->name ?? $this->myParcelCarrier->human;
        $psCarrier->active               = $this->psCarrierService->carrierIsActive($this->myParcelCarrier);
        $psCarrier->id_reference         = $this->createCarrierIdReference();
        $psCarrier->deleted              = false;
        $psCarrier->external_module_name = $module->name;
        $psCarrier->is_module            = true;
        $psCarrier->need_range           = true;
        $psCarrier->range_behavior       = true;
        $psCarrier->shipping_external    = true;
        $psCarrier->shipping_method      = 2;

        foreach (PsLanguage::getLanguages() as $lang) {
            $existingString = $psCarrier->delay[$lang['id_lang']] ?? null;
            $newString      = Language::translate('carrier_delivery_time', $lang['iso_code']);

            $psCarrier->delay[$lang['id_lang']] = $existingString ?? $newString;
        }

        $this->psCarrierService->updateOrAdd($psCarrier);

        $this->psCarrier = $psCarrier;
    }

    /**
     * @return int
     */
    private function createCarrierIdReference(): int
    {
        $carrierId = str_pad((string) $this->myParcelCarrier->id, 3, '0');

        return (int) ($carrierId . $this->myParcelCarrier->subscriptionId);
    }

    /**
     * @return null|\Carrier
     */
    private function getExistingPsCarrier(): ?PsCarrier
    {
        $mapping = $this->carrierMappingRepository->findOneBy([
            MyparcelnlCarrierMapping::MYPARCEL_CARRIER => $this->myParcelCarrier->externalIdentifier,
        ]);

        if ($mapping) {
            return $this->psCarrierService->get($mapping->getCarrierId());
        }

        return $this->psCarrierService->getByReference($this->createCarrierIdReference());
    }
}
