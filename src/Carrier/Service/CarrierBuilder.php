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
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use ObjectModel;
use RangePrice;
use RangeWeight;
use RuntimeException;
use Zone;

final class CarrierBuilder
{
    private const RANGE_CLASSES = [
        RangePrice::class,
        RangeWeight::class,
    ];

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
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $myParcelCarrier
     */
    public function __construct(Carrier $myParcelCarrier)
    {
        $this->myParcelCarrier          = $myParcelCarrier;
        $this->carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);
    }

    /**
     * @return \Carrier
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
            'carrierId'       => (int) $this->psCarrier->id,
            'myparcelCarrier' => $this->myParcelCarrier->externalIdentifier,
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addRanges(): void
    {
        /** @var RangeWeight|RangePrice $objectClass */
        foreach (self::RANGE_CLASSES as $objectClass) {
            $hasExistingRanges = $objectClass::getRanges($this->psCarrier->id);

            if ($hasExistingRanges) {
                continue;
            }

            $instance = new $objectClass();

            $instance->id_carrier = $this->psCarrier->id;
            $instance->delimiter1 = '0';
            $instance->delimiter2 = '10000';

            $this->updateOrAdd($instance);
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createCarrier(): void
    {
        /** @var \MyParcelNL $module */
        $module = Pdk::get('moduleInstance');

        $psCarrier = $this->getExistingPsCarrier() ?? new PsCarrier();

        $psCarrier->name                 = $this->myParcelCarrier->human;
        $psCarrier->active               = (int) $this->myParcelCarrier->enabled;
        $psCarrier->id_reference         = $this->createCarrierIdReference();
        $psCarrier->deleted              = 0;
        $psCarrier->external_module_name = $module->name;
        $psCarrier->is_module            = true;
        $psCarrier->need_range           = 1;
        $psCarrier->range_behavior       = 1;
        $psCarrier->shipping_external    = true;
        $psCarrier->shipping_method      = 2;

        foreach (PsLanguage::getLanguages() as $lang) {
            $psCarrier->delay[$lang['id_lang']] = Language::translate('carrier_delivery_time', $lang['iso_code']);
        }

        $this->updateOrAdd($psCarrier, (bool) $psCarrier->id);

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
        $mapping = $this->carrierMappingRepository
            ->findOneBy(['myparcelCarrier' => $this->myParcelCarrier->externalIdentifier]);

        if ($mapping) {
            return new PsCarrier($mapping->getCarrierId());
        }

        $existingCarrier = PsCarrier::getCarrierByReference($this->createCarrierIdReference());

        return $existingCarrier ?: null;
    }

    /**
     * @param  \ObjectModel $model
     * @param  bool         $existing
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function updateOrAdd(ObjectModel $model, bool $existing = false): void
    {
        $result = $existing ? $model->update() : $model->add();

        if (! $result) {
            throw new RuntimeException(sprintf('Could not %s %s', $existing ? 'update' : 'create', get_class($model)));
        }
    }
}
