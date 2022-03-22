<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Model\Core;

use Configuration;
use Exception;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Service\Concern\HasApiKey;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration;
use MyParcelNL\Sdk\src\Model\Account\CarrierOptions;
use MyParcelNL\Sdk\src\Model\Account\Shop;
use MyParcelNL\Sdk\src\Services\Web\AccountWebService;
use MyParcelNL\Sdk\src\Services\Web\CarrierConfigurationWebService;
use MyParcelNL\Sdk\src\Services\Web\CarrierOptionsWebService;
use MyParcelNL\Sdk\src\Services\Web\Webhook\ShopCarrierAccessibilityUpdatedWebhookWebService;
use MyParcelNL\Sdk\src\Services\Web\Webhook\ShopCarrierConfigurationUpdatedWebhookWebService;
use MyParcelNL\Sdk\src\Services\Web\Webhook\ShopUpdatedWebhookWebService;
use MyParcelNL\Sdk\src\Support\Collection;

class AccountSettingsService
{
    use HasApiKey;
    use HasInstance;

    /**
     * @throws \Exception
     */
    public function removeSettings(): void
    {
        $this->deleteSettingsFromDatabase();
    }

    /**
     * Load the account settings from the API, and save them to wp options.
     *
     * @return bool
     */
    public function refreshSettingsFromApi(): bool
    {
        try {
            $settings = $this->fetchFromApi();
            $this->saveSettingsToDatabase($settings);

            return true;
        } catch (Exception $e) {
            ApiLogger::addLog('Could not load account settings');
            ApiLogger::addLog($e->getMessage());
        }

        return false;
    }

    /**
     * @param  bool $triedFetching
     *
     * @return null|\MyParcelNL\Sdk\src\Support\Collection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public function retrieveSettings(bool $triedFetching = false): ?Collection
    {
        $options = Configuration::get(AccountSettings::ACCOUNT_SETTINGS_CONFIGURATION_NAME);

        if (! $options && ! $triedFetching) {
            if (! $this->refreshSettingsFromApi()) {
                return null;
            }
            return $this->retrieveSettings(true) ?? new Collection();
        }

        return new Collection(json_decode($options, true));
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $settings
     *
     * @return array
     * @TODO sdk#326 remove this entire function and replace with toArray
     */
    private function createArray(Collection $settings): array
    {
        /** @var \MyParcelNL\Sdk\src\Model\Account\Shop $shop */
        $shop = $settings->get('shop');
        /** @var \MyParcelNL\Sdk\src\Model\Account\Account $account */
        $account = $settings->get('account');
        /** @var \MyParcelNL\Sdk\src\Model\Account\CarrierOptions[]|Collection $carrierOptions */
        $carrierOptions = $settings->get('carrier_options');
        /** @var \MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration[]|Collection $carrierConfigurations */
        $carrierConfigurations = $settings->get('carrier_configurations');

        return [
            'shop'                   => [
                'id'   => $shop->getId(),
                'name' => $shop->getName(),
            ],
            'account'                => $account->toArray(),
            'carrier_options'        => array_map(static function (CarrierOptions $carrierOptions) {
                $carrier = $carrierOptions->getCarrier();
                return [
                    'carrier'  => [
                        'human' => $carrier->getHuman(),
                        'id'    => $carrier->getId(),
                        'name'  => $carrier->getName(),
                    ],
                    'enabled'  => $carrierOptions->isEnabled(),
                    'label'    => $carrierOptions->getLabel(),
                    'optional' => $carrierOptions->isOptional(),
                ];
            }, $carrierOptions->all()),
            'carrier_configurations' => array_map(static function (CarrierConfiguration $carrierConfiguration) {
                $defaultDropOffPoint = $carrierConfiguration->getDefaultDropOffPoint();
                $carrier             = $carrierConfiguration->getCarrier();
                return [
                    'carrier_id'                        => $carrier->getId(),
                    'default_drop_off_point'            => $defaultDropOffPoint ? [
                        'box_number'        => $defaultDropOffPoint->getBoxNumber(),
                        'cc'                => $defaultDropOffPoint->getCc(),
                        'city'              => $defaultDropOffPoint->getCity(),
                        'location_code'     => $defaultDropOffPoint->getLocationCode(),
                        'location_name'     => $defaultDropOffPoint->getLocationName(),
                        'number'            => $defaultDropOffPoint->getNumber(),
                        'number_suffix'     => $defaultDropOffPoint->getNumberSuffix(),
                        'postal_code'       => $defaultDropOffPoint->getPostalCode(),
                        'region'            => $defaultDropOffPoint->getRegion(),
                        'retail_network_id' => $defaultDropOffPoint->getRetailNetworkId(),
                        'state'             => $defaultDropOffPoint->getState(),
                        'street'            => $defaultDropOffPoint->getStreet(),
                    ] : null,
                    'default_drop_off_point_identifier' => $carrierConfiguration->getDefaultDropOffPointIdentifier(),
                ];
            }, $carrierConfigurations->all()),
        ];
    }

    /**
     * @return bool
     */
    private function deleteSettingsFromDatabase(): bool
    {
        return Configuration::updateValue(AccountSettings::ACCOUNT_SETTINGS_CONFIGURATION_NAME, null);
    }

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    private function fetchFromApi(): Collection
    {
        $apiKey = $this->ensureHasApiKey();

        $accountService = (new AccountWebService())->setApiKey($apiKey);

        $account = $accountService->getAccount();
        $shop    = $account->getShops()
            ->first();

        $carrierOptionsService = (new CarrierOptionsWebService())->setApiKey($apiKey);
        $carrierOptions        = $carrierOptionsService->getCarrierOptions($shop->getId());

        $carrierConfigurationService = (new CarrierConfigurationWebService())->setApiKey($apiKey);
        $carrierConfigurations       = $this->loadCarrierConfigurations(
            $carrierConfigurationService,
            $shop
        );

        return new Collection([
            'shop'                   => $shop,
            'account'                => $account,
            'carrier_options'        => $carrierOptions,
            'carrier_configurations' => $carrierConfigurations,
        ]);
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Services\Web\CarrierConfigurationWebService $service
     * @param  \MyParcelNL\Sdk\src\Model\Account\Shop                          $shop
     *
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    private function loadCarrierConfigurations(
        CarrierConfigurationWebService $service,
        Shop                           $shop
    ):
    Collection {
        return $service->getCarrierConfigurations($shop->getId(), true);
    }

    /**
     * Save this object to wp options and return success.
     *
     * @param  \MyParcelNL\Sdk\src\Support\Collection $settings
     *
     * @return bool
     */
    private function saveSettingsToDatabase(Collection $settings): bool
    {
        $json = json_encode($this->createArray($settings));

        return Configuration::updateValue(AccountSettings::ACCOUNT_SETTINGS_CONFIGURATION_NAME, $json);
    }
}
