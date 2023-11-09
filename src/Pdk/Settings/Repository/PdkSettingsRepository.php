<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use Throwable;

class PdkSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                             $storage
     * @param  \MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface $configurationService
     */
    public function __construct(StorageInterface $storage, PsConfigurationServiceInterface $configurationService)
    {
        parent::__construct($storage);
        $this->configurationService = $configurationService;
    }

    /**
     * @param  string $namespace
     *
     * @return array|\MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel
     */
    public function getGroup(string $namespace)
    {
        return $this->configurationService->get($namespace);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function store(string $key, $value): void
    {
        if (null === $value) {
            Logger::debug("Deleting option $key");
            $this->configurationService->delete($key);

            return;
        }

        $this->configurationService->set($key, $value);
        $this->save($key, $value);
    }

    /**
     * @param  SettingsModelCollection $settings
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function storeSettings($settings): void
    {
        parent::storeSettings($settings);

        if ($settings->id !== CarrierSettings::ID) {
            return;
        }

        $this->onUpdateCarrierSettings($settings);
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection $collection
     *
     * @return void
     */
    private function onUpdateCarrierSettings(SettingsModelCollection $collection): void
    {
        $collection->each(function (CarrierSettings $settings, string $carrierIdentifier) {
            try {
                $this->updatePsCarrier($carrierIdentifier);
            } catch (Throwable $e) {
                Logger::error('Failed to update carrier', ['carrier' => $carrierIdentifier, 'exception' => $e]);
            }
        });
    }

    /**
     * Toggle carrier active state based on settings.
     *
     * @param  string $carrierIdentifier
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function updatePsCarrier(string $carrierIdentifier): void
    {
        /** @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface $carrierService */
        $carrierService = Pdk::get(PsCarrierServiceInterface::class);

        $carrier = new Carrier(['externalIdentifier' => $carrierIdentifier]);

        $psCarrier = $carrierService->getPsCarrier($carrier);

        if (! $psCarrier) {
            return;
        }

        $newActive = $carrierService->carrierIsActive($carrier);

        /** @noinspection PhpCastIsUnnecessaryInspection */
        if ((bool) $psCarrier->active === $newActive) {
            return;
        }

        $psCarrier->active = $newActive;
        $psCarrier->update();
    }
}
