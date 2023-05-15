<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Installer;

use Carrier;
use Context;
use Group;
use Language as PsLanguage;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Repository\PsCarrierConfigurationRepository;
use PrestaShop\PrestaShop\Adapter\Entity\Zone;
use PrestaShop\PrestaShop\Core\Foundation\Database\Exception;
use RangePrice;
use RangeWeight;
use RuntimeException;

/**
 * May only contain code that is needed for a bare installation as well as the PDK upgrade.
 *
 * @see \MyParcelNL\PrestaShop\Module\Upgrade\Migration2_0_0
 * @see \MyParcelNL\PrestaShop\Module\Installer\PsInstallerService
 */
final class PsPdkUpgradeService
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCarrierConfigurationRepository
     */
    private $carrierConfigurationRepository;

    public function __construct(PsCarrierConfigurationRepository $carrierConfigurationRepository)
    {
        $this->carrierConfigurationRepository = $carrierConfigurationRepository;
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return void
     */
    public function addZones(Carrier $carrier): void
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            if ($carrier->addZone($zone['id_zone'])) {
                continue;
            }

            throw new RuntimeException("Failed to add zone {$zone['id_zone']} to carrier {$carrier->id}");
        }
    }

    /**
     * Creates and maps one PrestaShop carrier to a MyParcel carrier.
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     * @todo maybe differentiate between installing from scratch and upgrading
     */
    public function createPsCarriers(): void
    {
        Logger::warning('Creating carriers');

        $carriers = Pdk::get('allowedCarriers');

        foreach ($carriers as $carrierName) {
            $carrier = $this->addCarrier($carrierName);

            $this->addGroups($carrier);
            $this->addRanges($carrier);
            $this->addZones($carrier);

            $carrier->update();
            $this->addCarrierConfiguration($carrier, $carrierName);
        }
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return void
     */
    protected function addGroups(Carrier $carrier): void
    {
        $groups = Group::getGroups(Context::getContext()->language->id);

        if ($carrier->setGroups(Arr::pluck($groups, 'id_group'))) {
            return;
        }

        throw new RuntimeException("Failed to add groups to carrier {$carrier->id}");
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return void
     */
    protected function addRanges(Carrier $carrier): void
    {
        foreach ([new RangePrice(), new RangeWeight()] as $item) {
            $item->id_carrier = $carrier->id;
            $item->delimiter1 = '0';
            $item->delimiter2 = '10000';

            if ($item->add()) {
                continue;
            }

            throw new RuntimeException("Failed to add range to carrier {$carrier->id}");
        }
    }

    /**
     * @param  string $carrierName
     *
     * @return Carrier
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    private function addCarrier(string $carrierName): Carrier
    {
        /** @var \MyParcelNL $module */
        $module = Pdk::get('moduleInstance');

        $appInfo = Pdk::getAppInfo();

        //        /** @var callable $createSettingsKey */
        //        $createSettingsKey = Pdk::get('createSettingsKey');
        //
        //        $carrierSettingKey = $createSettingsKey(sprintf("carrier_%s", $carrierName));

        // todo: check if carrier already exists using $carrierSettingKey, if so, update it instead of creating a new one

        $carrier = new Carrier();

        $carrier->name = sprintf('%s (%s)', $carrierName, $appInfo->title);
        /**
         * TODO: activate the carrier when account settings are present and we know it should be enabled
         *
         * @see \MyParcelNL\PrestaShop\Pdk\Plugin\Action\Backend\Account\PsUpdateAccountAction::updateAndSaveAccount
         */
        $carrier->active = 0;
        $carrier->external_module_name = $module->name;
        $carrier->is_module = true;
        $carrier->need_range = 1;
        $carrier->range_behavior = 1;
        $carrier->shipping_external = true;
        $carrier->shipping_method = 2;

        foreach (PsLanguage::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = Language::translate('delivery_time', $lang['iso_code']);
        }

        if (! $carrier->add()) {
            throw new Exception(sprintf('Cannot add carrier %s', $carrierName));
        }

        // TODO: save new carrier id to setting "$carrierSettingKey"

        Logger::warning('Created carrier', ['carrier' => $carrierName]);

        return $carrier;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function addCarrierConfiguration(Carrier $carrier, string $carrierName)
    {
        // todo fix this:
        $this->carrierConfigurationRepository->updateOrCreate(
            [
                'idCarrier' => (int) $carrier->id,
            ],
            [
                'myparcelCarrier' => $carrierName,
            ]
        );
    }
}
