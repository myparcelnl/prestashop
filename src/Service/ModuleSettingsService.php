<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Configuration;
use Gett\MyparcelBE\Concern\HasErrors;
use Gett\MyparcelBE\Module\Hooks\Helpers\ModuleSettings;

class ModuleSettingsService
{
    private $validKeys;
    use HasErrors;

    public function upsertModuleSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            // todo only convert when data type is bool, so you can still save the string 'true' if you want JOERI
            if ('true' === $value) {
                $value = '1';
            }
            if ('false' === $value) {
                $value = '0';
            }

            if (is_array($value)) {
                $value = implode(',', array_filter($value));
            }

            if (strpos($key, ModuleSettings::CARRIER_FIELD_SEPARATOR)) {
                [$psCarrierId, $fieldName] = explode(ModuleSettings::CARRIER_FIELD_SEPARATOR, $key);
                $success =  CarrierConfigurationProvider::upsertValue((int) $psCarrierId, $fieldName, $value);
            } else {
                $success = Configuration::updateValue($key, $value);
            }

            if (! $success) {
                throw new \RuntimeException(sprintf('Could not update %s', htmlentities($key)));
            }
        }
    }
}
