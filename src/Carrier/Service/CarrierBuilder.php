<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Carrier\Service;

use Carrier as PsCarrier;
use Context;
use Db;
use Group;
use Language as PsLanguage;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Database\Table;
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
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function create(): void
    {
        $this->psCarrier = $this->createCarrier();

        $this->addGroups();
        $this->addRanges();
        $this->addZones();

        $this->psCarrier->update();

        $this->addCarrierMapping();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function addCarrierMapping(): void
    {
        $this->carrierMappingRepository->updateOrCreate(
            [
                'myparcelCarrier' => $this->myParcelCarrier->externalIdentifier,
            ],
            [
                'idCarrier' => (int) $this->psCarrier->id,
            ]
        );
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
            $this->deleteExistingRanges($objectClass);

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
        $existingZones = Arr::pluck($this->psCarrier->getZones(), 'id_zone');

        foreach (Zone::getZones() as $zone) {
            if (in_array($zone['id_zone'], $existingZones, true)) {
                continue;
            }

            if ($this->psCarrier->addZone($zone['id_zone'])) {
                continue;
            }

            throw new RuntimeException("Failed to add zone {$zone['id_zone']} to carrier {$this->psCarrier->id}");
        }
    }

    /**
     * @return PsCarrier
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createCarrier(): PsCarrier
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

        // TODO: add logo

        foreach (PsLanguage::getLanguages() as $lang) {
            $psCarrier->delay[$lang['id_lang']] = Language::translate('carrier_delivery_time', $lang['iso_code']);
        }

        $this->updateOrAdd($psCarrier, (bool) $psCarrier->id);

        return $psCarrier;
    }

    /**
     * @return int
     */
    private function createCarrierIdReference(): int
    {
        $carrierId = str_pad((string) $this->myParcelCarrier->id, 3, '0');

        return (int) ($carrierId . $this->myParcelCarrier->externalIdentifier);
    }

    /**
     * @param  class-string<RangeWeight|RangePrice> $objectClass
     *
     * @return void
     */
    private function deleteExistingRanges(string $objectClass): void
    {
        $existing = $objectClass::getRanges($this->psCarrier->id);

        if (! $existing) {
            return;
        }

        $definition = $objectClass::getDefinition($objectClass);
        $table      = Table::withPrefix($definition['table']);

        Db::getInstance()
            ->execute("DELETE FROM `$table` WHERE `id_carrier` = {$this->psCarrier->id}");
    }

    /**
     * @return null|\Carrier
     */
    private function getExistingPsCarrier(): ?PsCarrier
    {
        $mapping = $this->carrierMappingRepository
            ->findOneBy(['myparcelCarrier' => $this->myParcelCarrier->externalIdentifier]);

        return $mapping ? new PsCarrier($mapping->idCarrier) : null;
    }

    /**
     * @param  int    $id
     * @param  string $class
     *
     * @return \ObjectModel
     */
    private function getOrCreateModel(int $id, string $class): ObjectModel
    {
        return new $class($id) ?? new $class();
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
