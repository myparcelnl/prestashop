<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Settings\Repository;

use Gett\MyparcelBE\Constant;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;

class PdkSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getSettings(): Settings
    {
        return new Settings([
            GeneralSettings::ID  => $this->getGeneralSettings(),
            OrderSettings::ID    => [],
            LabelSettings::ID    => [],
            CustomsSettings::ID  => [],
            CheckoutSettings::ID => $this->getCheckoutSettings(),
            CarrierSettings::ID  => [],
        ]);
    }

    /**
     * @return array
     */
    protected function getCheckoutSettings(): array
    {
        return [
            CheckoutSettings::PRICE_TYPE               => \Configuration::get(
                Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME
            ),
            CheckoutSettings::SHOW_DELIVERY_DAY        => true,
            CheckoutSettings::STRING_ADDRESS_NOT_FOUND => '',
            CheckoutSettings::STRING_CITY              => '',
            CheckoutSettings::STRING_COUNTRY           => '',
            CheckoutSettings::STRING_DELIVERY          => '',
            CheckoutSettings::STRING_DISCOUNT          => '',
            CheckoutSettings::STRING_EVENING_DELIVERY  => '',
            CheckoutSettings::STRING_FROM              => '',
            CheckoutSettings::STRING_HOUSE_NUMBER      => '',
            CheckoutSettings::STRING_LOAD_MORE         => '',
            CheckoutSettings::STRING_MORNING_DELIVERY  => '',
        ];
    }

    /**
     * @return array
     */
    protected function getGeneralSettings(): array
    {
        return [
            GeneralSettings::API_KEY                    => \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            GeneralSettings::API_LOGGING                => \Configuration::get(
                Constant::API_LOGGING_CONFIGURATION_NAME
            ),
            GeneralSettings::BARCODE_IN_NOTE            => false,
            GeneralSettings::CONCEPT_SHIPMENTS          => \Configuration::get(Constant::CONCEPT_FIRST),
            GeneralSettings::ORDER_MODE                 => false,
            GeneralSettings::PROCESS_DIRECTLY           => false,
            GeneralSettings::SHARE_CUSTOMER_INFORMATION => \Configuration::get(
                Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME
            ),
            GeneralSettings::TRACK_TRACE_IN_ACCOUNT     => false,
            GeneralSettings::TRACK_TRACE_IN_EMAIL       => false,
        ];
    }

    public function store(Settings $settings): void
    {
        // TODO: Implement store() method.
    }
}
