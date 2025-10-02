<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use Context;
use MyParcelNL;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\PrestaShop\Carrier\Service\CarrierBuilder;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
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
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    public function carrierIsActive(Carrier $carrier): bool
    {
        if (! $carrier->enabled) {
            return false;
        }

        $deliveryOptionsEnabled = Settings::get(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, CheckoutSettings::ID);

        if (! $deliveryOptionsEnabled) {
            return false;
        }

        $settings = Settings::get($carrier->externalIdentifier, CarrierSettings::ID);

        $allowDeliveryOptions = Arr::get($settings, CarrierSettings::ALLOW_DELIVERY_OPTIONS);
        $allowPickupLocations = Arr::get($settings, CarrierSettings::ALLOW_PICKUP_LOCATIONS);

        return Arr::get($settings, CarrierSettings::DELIVERY_OPTIONS_ENABLED)
            && ($allowDeliveryOptions || $allowPickupLocations);
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
                $created = $builder->create();

                Logger::debug(sprintf('Created carrier %s', $carrier->externalIdentifier));

                return $created;
            }
        );
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
        $match       = $this->carrierMappingRepository->firstWhere(MyparcelnlCarrierMapping::CARRIER_ID, $psCarrierId);

        return $match ? $match->getMyparcelCarrier() : null;
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $myParcelCarrier
     *
     * @return null|\Carrier
     */
    public function getPsCarrier(Carrier $myParcelCarrier): ?PsCarrier
    {
        $match = $this->carrierMappingRepository->firstWhere(
            MyparcelnlCarrierMapping::MYPARCEL_CARRIER,
            $myParcelCarrier->externalIdentifier
        );

        return $match ? $this->get($match->getCarrierId()) : null;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection<PsCarrier>
     */
    public function getPsCarriers(): Collection
    {
        $carrierArray = PsCarrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            null,
            null,
            PsCarrier::CARRIERS_MODULE
        );

        return (new Collection($carrierArray))->map(function (array $item) {
            return new PsCarrier($item['id_carrier']);
        });
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
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $createdCarriers
     *
     * @return void
     */
    protected function deleteUnusedCarriers(Collection $createdCarriers): void
    {
        $psCarriers = $this->getPsCarriers();
        $moduleName = MyParcelNL::MODULE_NAME;

        $psCarriers
            ->filter(function (PsCarrier $psCarrier) use ($moduleName, $createdCarriers): bool {
                $isOurs = $psCarrier->external_module_name === $moduleName;

                return $isOurs && ! $createdCarriers->contains('id', $this->getId($psCarrier));
            })
            ->each(function (PsCarrier $psCarrier): void {
                $this->delete($psCarrier, true);
            });
    }

    protected function getClass(): string
    {
        return PsCarrier::class;
    }
}
