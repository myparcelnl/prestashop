<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Carrier;
use Cart;
use Country;
use MyParcelNL\Pdk\Base\Model\Address as PdkAddress;
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

        $cart      = $params['cart'] ?? $this->context->cart ?? new Cart();
        $country   = $this->getCountryFromCart($cart);
        $supported = $this->getSupportedCarriersForCountry((string) $country->iso_code, $this->cartIsBusiness($cart));

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

                // Stored mappings may include a :contractId suffix (e.g. "DHL_FOR_YOU:1234").
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
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayCarrierList(array $params): string
    {
        return '';
    }

    /**
     * Returns the V2 carrier names that capabilities lists as supported for the
     * given destination country, or null if the API call failed (fail-open signal).
     *
     * @param  string $countryIso ISO 3166-1 alpha-2 destination country code
     * @param  bool   $isBusiness Whether the cart's recipient is a business
     *
     * @return null|string[]
     */
    private function getSupportedCarriersForCountry(string $countryIso, bool $isBusiness): ?array
    {
        /** @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository $repository */
        $repository = Pdk::get(CarrierCapabilitiesRepository::class);

        try {
            $capabilities = $repository->getCapabilities([
                'recipient' => ['country_code' => $countryIso, 'is_business' => $isBusiness],
            ]);
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

        /** @var null|\Carrier $psCarrier */
        $psCarrier = $carrierArray['instance'] ?? null;

        if (! $psCarrier instanceof Carrier) {
            return null;
        }

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

    /**
     * Whether the cart's delivery address is a business, derived from its company name via the
     * PDK Address rule so detection stays in one place. No delivery address or company → consumer.
     *
     * @param  \Cart $cart
     *
     * @return bool
     */
    private function cartIsBusiness(Cart $cart): bool
    {
        if (! $cart->id_address_delivery) {
            return false;
        }

        $company = (new Address($cart->id_address_delivery))->company;

        return (new PdkAddress(['company' => $company]))->isBusiness;
    }
}
