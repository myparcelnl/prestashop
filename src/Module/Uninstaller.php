<?php

namespace Gett\MyparcelBE\Module;

use Carrier;
use Configuration;
use Db;
use DbQuery;
use Gett\MyparcelBE\Constant;
use MyParcelBE;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;

class Uninstaller
{
    /**
     * @var \MyParcelBE
     */
    private $module;

    public function __construct()
    {
        $this->module = MyParcelBE::getModule();
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function uninstall(): bool
    {
        return $this->hooks()
            && $this->migrate()
            && $this->uninstallTabs()
            && $this->removeCarriers()
            && $this->removeConfigurations();
    }

    private function hooks(): bool
    {
        $result = true;
        foreach ($this->module->hooks as $hook) {
            $result &= $this->module->unregisterHook($hook);
        }

        return $result;
    }

    private function migrate(): bool
    {
        $result = true;
        foreach ($this->module->migrations as $migration) {
            $result &= $migration::down();
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstallTabs(): bool
    {
        $status = true;
        $query  = new DbQuery();
        $query->select('id_tab');
        $query->from('tab');
        $query->where(sprintf("module = '%s'", MyParcelBE::MODULE_NAME));
        $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($query);

        foreach (Arr::pluck($ids, 'id_tab') as $tabId) {
            $tab    = new Tab($tabId);
            $status &= $tab->delete();
        }

        return $status;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function removeCarriers(): bool
    {
        $result = true;
        $carrierListConfig = [
            Constant::POSTNL_CONFIGURATION_NAME,
            Constant::BPOST_CONFIGURATION_NAME,
            Constant::DPD_CONFIGURATION_NAME,
        ];
        foreach ($carrierListConfig as $item) {
            $idReference = Configuration::get($item);
            $query = new DbQuery();
            $query->select('id_carrier');
            $query->from('carrier');
            $query->where('id_reference = ' . (int) $idReference);
            $idCarrier = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
            if ($idCarrier) {
                $carrier = new Carrier($idCarrier);
                $carrier->deleted = 1;
                $result &= $carrier->update();
            }
        }

        return $result;
    }

    private function removeConfigurations(): bool
    {
        foreach ($this->module->configItems as $configItem) {
            Configuration::deleteByName($configItem);
        }

        return true;
    }
}
