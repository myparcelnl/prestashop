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
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;

final class PsCarrierService implements PsCarrierServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository
     */
    private $carrierMappingRepository;

    /**
     * @param  \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $carrierMappingRepository
     */
    public function __construct(PsCarrierMappingRepository $carrierMappingRepository)
    {
        $this->carrierMappingRepository = $carrierMappingRepository;
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
        $psCarriers = new Collection($this->getPsCarriers());

        $psCarriers->where('external_module_name', Pdk::getAppInfo()->name)
            ->each(function (array $carrier): void {
                $psCarrier = new PsCarrier($carrier['id_carrier']);

                $psCarrier->active = false;

                if (! $psCarrier->softDelete()) {
                    Logger::error("Failed to soft delete carrier {$carrier['id_carrier']}");

                    return;
                }

                Logger::debug("Soft deleted carrier {$carrier['id_carrier']}");
            });
    }

    /**
     * @param  int|PsCarrier $input
     *
     * @return PsCarrier
     */
    public function get($input): PsCarrier
    {
        if ($input instanceof PsCarrier) {
            return $input;
        }

        return new PsCarrier($input);
    }

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|int|\Carrier
     */
    public function getId($input): int
    {
        return $input instanceof PsCarrier ? $input->id : $input;
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
     */
    protected function deleteUnusedCarriers(Collection $createdCarriers): void
    {
        $psCarriers = new Collection($this->getPsCarriers());
        $moduleName = Pdk::getAppInfo()->name;

        $psCarriers
            ->filter(function (array $carrier) use ($moduleName, $createdCarriers): bool {
                return $carrier['external_module_name'] === $moduleName
                    && ! $createdCarriers->contains('id', $carrier['id_carrier']);
            })
            ->each(static function (array $carrier): void {
                $psCarrier = new PsCarrier($carrier['id_carrier']);

                if (! $psCarrier->delete()) {
                    Logger::error("Failed to delete carrier {$carrier['id_carrier']}");

                    return;
                }

                Logger::debug("Deleted carrier {$carrier['id_carrier']}");
            });
    }

    /**
     * @return array
     */
    private function getPsCarriers(): array
    {
        return PsCarrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            null,
            null,
            PsCarrier::CARRIERS_MODULE
        );
    }
}
