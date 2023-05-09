<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Concern;

use Carrier;
use Configuration;
use Db;
use DbQuery;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Database\DatabaseMigrations;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;
use RuntimeException;

trait HasModuleUninstall
{
    //    private $configItems = [
    //        Constant::POSTNL_CONFIGURATION_NAME,
    //        Constant::BPOST_CONFIGURATION_NAME,
    //        Constant::DPD_CONFIGURATION_NAME,
    //
    //        Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME,
    //        Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
    //        Constant::LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME,
    //        Constant::DELIVERED_ORDER_STATUS_CONFIGURATION_NAME,
    //        Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME,
    //
    //        Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME,
    //        Constant::WEBHOOK_ID_CONFIGURATION_NAME,
    //
    //        Constant::API_LOGGING_CONFIGURATION_NAME, // Keep the API key
    //
    //        Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
    //        Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
    //        Constant::AGE_CHECK_CONFIGURATION_NAME,
    //        Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,
    //
    //        Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
    //        Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
    //        Constant::INSURANCE_CONFIGURATION_NAME,
    //        Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
    //        Constant::CUSTOMS_CODE_CONFIGURATION_NAME,
    //        Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME,
    //        Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME,
    //        Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME,
    //
    //        Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
    //        Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME,
    //
    //        Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME,
    //        Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
    //        Constant::LABEL_SIZE_CONFIGURATION_NAME,
    //        Constant::LABEL_POSITION_CONFIGURATION_NAME,
    //        Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME,
    //
    //        Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME,
    //    ];

    /**
     * @var int
     */
    private $uninstallSuccess = 1;

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Throwable
     */
    public function executeUninstall(): bool
    {
        DefaultLogger::debug('Uninstalling module');

        $this->unregisterHooks();
        $this->migrateDown();
        $this->uninstallTabs();
        //        $this->removeCarriers();
        //        $this->removeConfigurations();

        if (! empty($this->errors)) {
            throw new RuntimeException(
                sprintf('One or more errors occurred while uninstalling: %s', implode(', ', $this->errors))
            );
        }

        return (bool) $this->uninstallSuccess;
    }

    private function migrateDown(): void
    {
        /** @var \MyParcelNL\PrestaShop\Database\DatabaseMigrations $migrations */
        $migrations = Pdk::get(DatabaseMigrations::class);

        foreach ($migrations->get() as $migration) {
            /** @var \MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration $class */
            $class  = Pdk::get($migration);
            $result = $class->down();

            if (! $result) {
                $this->_errors[] = "Failed to execute migration: $migration";
                DefaultLogger::error('Failed to execute migration', ['migration' => $migration]);
            }

            $this->uninstallSuccess &= $result;
        }
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function removeCarriers(): void
    {
        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('carrier');
        $query->where("external_module_name = '$this->name'");
        $carriers = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($query);

        foreach ($carriers as $carrier) {
            $carrierInstance          = new Carrier($carrier['id_carrier']);
            $carrierInstance->deleted = 1;
            $result                   = $carrierInstance->update();

            if (! $result) {
                $this->_errors[] = "Failed to remove carrier: $carrier->name";
                DefaultLogger::error('Failed to remove carrier', ['carrier' => $carrier]);
            }

            $this->uninstallSuccess &= $result;
        }
    }

    /**
     * @return void
     */
    private function removeConfigurations(): void
    {
        foreach ($this->configItems as $item) {
            $result = Configuration::deleteByName($item);

            if (! $result) {
                $this->_errors[] = "Failed to remove configuration: $item";
                DefaultLogger::warning('Failed to remove configuration', ['item' => $item]);
            }

            $this->uninstallSuccess &= $result;
        }
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function uninstallTabs(): void
    {
        $query = new DbQuery();
        $query->select('id_tab');
        $query->from('tab');
        $query->where(sprintf("module = '%s'", MyParcelNL::MODULE_NAME));

        $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($query);

        foreach (Arr::pluck($ids, 'id_tab') as $tabId) {
            $tab    = new Tab($tabId);
            $result = $tab->delete();

            if (! $result) {
                $this->_errors[] = "Failed uninstalling tab: $tabId";
                DefaultLogger::error('Failed uninstalling tab', ['tab' => $tabId]);
            }

            $this->uninstallSuccess &= $result;
        }
    }

    private function unregisterHooks(): void
    {
        foreach (ModuleService::getHooks() as $hook) {
            $result = $this->unregisterHook($hook);

            if (! $result) {
                $this->_errors[] = "Failed to unregister hook: $hook";
                DefaultLogger::error('Failed to unregister hook', ['hook' => $hook]);
            }
        }
    }
}
