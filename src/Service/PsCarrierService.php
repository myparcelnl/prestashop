<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use Context;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Carrier\Service\CarrierBuilder;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;

/**
 * @template T of PsCarrier
 * @extends \MyParcelNL\PrestaShop\Service\PsSpecificObjectModelService<T>
 */
final class PsCarrierService extends PsSpecificObjectModelService implements PsCarrierServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository
     */
    private $carrierMappingRepository;

    /**
     * @param  \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface $psObjectModelService
     * @param  \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository  $psCarrierMappingRepository
     */
    public function __construct(
        PsObjectModelServiceInterface $psObjectModelService,
        PsCarrierMappingRepository    $psCarrierMappingRepository
    ) {
        parent::__construct($psObjectModelService);
        $this->carrierMappingRepository = $psCarrierMappingRepository;
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection $carriers
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function createOrUpdateCarriers(CarrierCollection $carriers): Collection
    {
        return (new Collection($carriers))->map(
            static function (Carrier $carrier): PsCarrier {
                $builder = new CarrierBuilder($carrier);

                Logger::info('Created carrier ' . $carrier->externalIdentifier);

                return $builder->create();
            }
        );
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function disableCarriers(): void
    {
        $psCarriers = $this->getPsCarriers();

        $psCarriers->where('external_module_name', Pdk::getAppInfo()->name)
            ->each(function (array $carrier): void {
                $psCarrier = $this->get($carrier['id_carrier']);

                $psCarrier->active = false;

                if (! $psCarrier->softDelete()) {
                    Logger::error("Failed to soft delete carrier {$carrier['id_carrier']}");

                    return;
                }

                Logger::debug("Soft deleted carrier {$carrier['id_carrier']}");
            });
    }

    /**
     * @param  int $reference
     *
     * @return null|PsCarrier
     */
    public function getByReference(int $reference): ?PsCarrier
    {
        return PsCarrier::getCarrierByReference($reference) ?: null;
    }

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function getMyParcelCarrier($input): ?Carrier
    {
        $identifier = $this->getMyParcelCarrierIdentifier($input);

        return $identifier ? new Carrier(['externalIdentifier' => $identifier]) : null;
    }

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|string
     */
    public function getMyParcelCarrierIdentifier($input): ?string
    {
        $psCarrierId = $this->getId($input);
        $match       = $this->carrierMappingRepository->firstWhere('carrierId', $psCarrierId);

        return $match ? $match->getMyparcelCarrier() : null;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getPsCarriers(): Collection
    {
        return new Collection(
            PsCarrier::getCarriers(
                Context::getContext()->language->id,
                false,
                false,
                null,
                null,
                PsCarrier::CARRIERS_MODULE
            )
        );
    }

    /**
     * @param  int|PsCarrier $input
     *
     * @return bool
     */
    public function isMyParcelCarrier($input): bool
    {
        return (bool) $this->getMyParcelCarrierIdentifier($input);
    }

    /**
     * @return void
     * @throws \PrestaShopException
     */
    public function updateCarriers(): void
    {
        $carriers = AccountSettings::getCarriers();

        $createdCarriers = $this->createOrUpdateCarriers($carriers);
        $this->deleteUnusedCarriers($createdCarriers);

        // Refresh the hooks
        MyParcelModule::registerHooks();
        EntityManager::flush();
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $createdCarriers
     *
     * @return void
     * @throws \PrestaShopException
     */
    protected function deleteUnusedCarriers(Collection $createdCarriers): void
    {
        $psCarriers = $this->getPsCarriers();
        $moduleName = Pdk::getAppInfo()->name;

        $psCarriers
            ->filter(function (array $carrier) use ($moduleName, $createdCarriers): bool {
                return $carrier['external_module_name'] === $moduleName
                    && ! $createdCarriers->contains('id', $carrier['id_carrier']);
            })
            ->each(function (array $carrier): void {
                $this->delete($carrier['id_carrier']);
            });
    }

    protected function getClass(): string
    {
        return PsCarrier::class;
    }
}
