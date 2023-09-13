<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Carrier\Service\CarrierBuilder;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
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
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createOrUpdateCarriers(CarrierCollection $carriers): void
    {
        $carriers->each(static function (Carrier $carrier): void {
            $builder = new CarrierBuilder($carrier);

            $builder->create();
        });

        Logger::debug(
            'Created carriers',
            [
                'carriers' => $carriers
                    ->pluck('externalIdentifier')
                    ->toArray(),
            ]
        );
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteCarriers(): void
    {
        $mappings = $this->carrierMappingRepository->all();

        $mappings->each(function (MyparcelnlCarrierMapping $carrier): void {
            $psCarrier = new PsCarrier($carrier->idCarrier);

            if (! $psCarrier->delete()) {
                Logger::error('Failed to delete carrier', [
                    'id'              => $carrier->idCarrier,
                    'myParcelCarrier' => $carrier->myparcelCarrier,
                ]);

                return;
            }

            $this->carrierMappingRepository->delete($carrier);

            Logger::debug('Deleted carrier', [
                'id'              => $carrier->idCarrier,
                'myParcelCarrier' => $carrier->myparcelCarrier,
            ]);
        });
    }

    /**
     * @param $carriers
     *
     * @return void
     */
    public function deleteUnusedCarriers($carriers): void
    {
        // delete other carriers
        $mappings = $this->carrierMappingRepository->all();

        $mappings
            ->filter(static function (MyparcelnlCarrierMapping $entity) use ($carriers): bool {
                return ! $carriers->containsStrict('externalIdentifier', $entity->myparcelCarrier);
            })
            ->each(static function (MyparcelnlCarrierMapping $mapping): void {
                $psCarrier = new PsCarrier($mapping->idCarrier);

                $context = [
                    'id'              => $mapping->idCarrier,
                    'myParcelCarrier' => $mapping->myparcelCarrier,
                ];

                if (! $psCarrier->delete()) {
                    Logger::error('Failed to delete carrier', $context);

                    return;
                }

                Logger::debug('Deleted carrier', $context);
            });
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function updateCarriers(): void
    {
        $carriers = AccountSettings::getCarriers();

        $this->createOrUpdateCarriers($carriers);
        $this->deleteUnusedCarriers($carriers);
    }
}
