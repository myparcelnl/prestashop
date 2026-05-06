<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Cart;
use Country;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPsCarrierListHooks
{
    /**
     * Filter carriers from the checkout delivery-option list using the MyParcel
     * capabilities API: a carrier is kept iff capabilities reports it for the cart's
     * destination country.
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

        $country   = $this->getCountryFromCart($params['cart'] ?? $this->context->cart ?? new Cart());
        $supported = $this->getSupportedCarriersForCountry((string) $country->iso_code);

        if ($supported === null) {
            // Capabilities call failed — fail-open, keep every carrier visible.
            return;
        }

        $deliveryOptionList = $params['delivery_option_list'] ?? [];

        foreach ($deliveryOptionList as $addressId => $item) {
            foreach ($item as $key => $value) {
                $carrierMapping = $this->getCarrierMapping($value['carrier_list'] ?? [], $mappings);

                if (! $carrierMapping) {
                    continue;
                }

                [$v2Name] = explode(':', $carrierMapping->getMyparcelCarrier(), 2);

                if (in_array($v2Name, $supported, true)) {
                    continue;
                }

                unset($params['delivery_option_list'][$addressId][$key]);
            }
        }
    }

    /**
     * Hook for displaying content in the carrier list. Filtering is handled by
     * hookActionFilterDeliveryOptionList; this hook is registered for compatibility
     * but produces no output.
     *
     * @param  array $_params
     *
     * @return string
     */
    public function hookDisplayCarrierList(array $_params): string
    {
        return '';
    }

    /**
     * Returns the V2 carrier names that capabilities lists as supported for the
     * given destination country, or null if the API call failed (fail-open signal).
     *
     * @param  string $countryIso ISO 3166-1 alpha-2 destination country code
     *
     * @return null|string[]
     */
    private function getSupportedCarriersForCountry(string $countryIso): ?array
    {
        /** @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository $repository */
        $repository = Pdk::get(CarrierCapabilitiesRepository::class);

        try {
            $capabilities = $repository->getCapabilitiesForRecipientCountry($countryIso);
        } catch (Throwable $exception) {
            Logger::error('Failed to fetch carrier capabilities for country.', [
                'country' => $countryIso,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        $names = [];

        foreach ($capabilities as $capability) {
            $names[(string) $capability->getCarrier()] = true;
        }

        return array_keys($names);
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

        /** @var \Carrier $psCarrier */
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
