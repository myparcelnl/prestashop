<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Cart;
use Country;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsCountryServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;

/**
 * @property \Context $context
 */
trait HasPsCarrierListHooks
{
    /**
     * Filters carriers from checkout based on country and if the carrier can ship there.
     *
     * @param  array $params
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionFilterDeliveryOptionList(array &$params): void
    {
        /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $carrierMappingRepository */
        $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);
        $mappings                 = $carrierMappingRepository->all();

        if ($mappings->isEmpty()) {
            return;
        }

        $country            = $this->getCountryFromCart($params['cart'] ?? $this->context->cart ?? new Cart());
        $deliveryOptionList = $params['delivery_option_list'] ?? [];

        foreach ($deliveryOptionList as $addressId => $item) {
            foreach ($item as $key => $value) {
                $carrierMapping = $this->getCarrierMapping($value['carrier_list'] ?? [], $mappings);

                if (! $carrierMapping) {
                    continue;
                }

                $carrierName       = $carrierMapping->getMyparcelCarrier();
                $allowedCountryIds = $this->getAllowedCountryIdsForCarrier($carrierName);

                if (in_array($country->id, array_values($allowedCountryIds), true)) {
                    continue;
                }

                // Delete the carrier from the list if it can't ship to the current country.
                unset($params['delivery_option_list'][$addressId][$key]);
            }
        }
    }

    /**
     * @param  string $carrierName
     *
     * @return int[]
     */
    private function getAllowedCountryIdsForCarrier(string $carrierName): array
    {
        /** @var \MyParcelNL\PrestaShop\Contract\PsCountryServiceInterface $psCountryService */
        $psCountryService = Pdk::get(PsCountryServiceInterface::class);

        $isoCodes = $psCountryService->getCountriesForCarrier($carrierName);

        return $psCountryService->getCountryIdsByIsoCodes($isoCodes);
    }

    /**
     * @param  array                                   $carrierList
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $mappings
     *
     * @return null|\MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping
     */
    private function getCarrierMapping(
        array      $carrierList,
        Collection $mappings
    ): ?MyparcelnlCarrierMapping {
        $carrierArray = Arr::first($carrierList);

        /** @var \Carrier $instance */
        $psCarrier = $carrierArray['instance'] ?? null;

        return $mappings
            ->filter(function (MyparcelnlCarrierMapping $mapping) use ($psCarrier) {
                return $mapping->getCarrierId() === $psCarrier->id;
            })
            ->first();
    }

    /**
     * @param  \Cart $cart
     *
     * @return \Country
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getCountryFromCart(Cart $cart): Country
    {
        $configurationService = Pdk::get(PsConfigurationServiceInterface::class);

        if ($cart->id_address_delivery) {
            $address = new Address($cart->id_address_delivery);
            $country = new Country($address->id_country);
        } else {
            $country = $this->context->country ?? new Country((int) $configurationService->get('PS_COUNTRY_DEFAULT'));
        }

        return $country;
    }
}
