<?php

namespace Gett\MyparcelBE\Module;

use Carrier;
use Configuration;
use Db;
use DbQuery;
use Gett\MyparcelBE\Constant;
use MyParcelBE;
use Tab;

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

    public function __invoke(): bool
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
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function uninstallTabs(): bool
    {
        $status = true;
        $tabs   = Installer::getAdminTabsDefinition();

        foreach ($tabs as $adminTab) {
            $tabId = (int) Tab::getIdFromClassName($adminTab['class_name']);

            if ($tabId) {
                $tab    = new Tab($tabId);
                $status &= $tab->delete();
            }
        }

        return $status;
    }

    private function removeCarriers()
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
