<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Installer;

use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsCarrierConfigurationRepository;
use PrestaShop\PrestaShop\Core\Foundation\Database\Exception;

/**
 * May only contain code that is needed for a bare installation as well as the PDK upgrade.
 *
 * @see \MyParcelNL\PrestaShop\Module\Upgrade\Migration2_0_0
 * @see \MyParcelNL\PrestaShop\Module\Installer\PsInstallerService
 */
final class PsPdkUpgradeService
{
    /**
     * Creates and maps one PrestaShop carrier to a MyParcel carrier.
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    public function createPsCarriers(): void
    {
        DefaultLogger::warning('Creating carriers');

        $db      = \Db::getInstance(_PS_USE_SQL_SLAVE_);
        $appInfo = Pdk::getAppInfo();

        // TODO: do not
        $carriers   = ['PostNL' => 'postnl'];
        $carrierIds = [];

        foreach ($carriers as $carrierHuman => $carrierName) {
            $nameField = sprintf('%s %s', $carrierHuman, $appInfo->name);
            $result    = $db->insert('carrier', [
                'name'                 => $nameField,
                'active'               => 1,
                'is_module'            => 1,
                'shipping_external'    => 1,
                'need_range'           => 1,
                'external_module_name' => $appInfo->name,
            ]);

            if (! $result) {
                throw new Exception('Cannot insert new carrier with name', compact($carrierName));
            }

            DefaultLogger::debug('Created carrier', ['carrier' => $carrierName]);

            // TODO kijk of dit zonder sql kan!!!
            $request = "SELECT id_carrier FROM ps_carrier WHERE name = '$nameField'";

            $carrierId = $db->getValue($request);

            if (! $carrierId) {
                throw new Exception('Cannot retrieve carrierId while upgrading', compact($request));
            }

            $carrierIds[$carrierName] = $carrierId;

            /** @var PsCarrierConfigurationRepository $carrierConfigurationRepository */
            $carrierConfigurationRepository = Pdk::get(PsCarrierConfigurationRepository::class);

            $carrierConfigurationRepository->create([
                'idCarrier'       => $carrierId,
                'myparcelCarrier' => $carrierName,
            ]);

            DefaultLogger::debug('Created carrier configuration', ['carrier' => $carrierName]);
        }
    }
}
