<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Concern;

use AdminMyParcelNLController;
use Carrier;
use Configuration;
use Context;
use Db;
use DbQuery;
use Group;
use Language;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Database\Migrations;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
use ObjectModel;
use RangePrice;
use RangeWeight;
use Tab;
use Zone;

trait HasModuleInstall
{
    private static $carriers_be = [
        [
            'name'               => 'Bpost',
            'image'              => 'bpost.jpg',
            'configuration_name' => 'BPOST_CONFIGURATION_NAME',
            'carrier_type'       => MyParcelNL\Pdk\Carrier\Model\Carrier::CARRIER_BPOST_NAME,
        ],
        [
            'name'               => 'DPD',
            'image'              => 'dpd.jpg',
            'configuration_name' => 'DPD_CONFIGURATION_NAME',
            'carrier_type'       => MyParcelNL\Pdk\Carrier\Model\Carrier::CARRIER_DPD_NAME,
        ],
    ];

    private static $carriers_nl = [
        [
            'name'               => 'PostNL',
            'image'              => 'postnl.jpg',
            'configuration_name' => 'POSTNL_CONFIGURATION_NAME',
            'carrier_type'       => MyParcelNL\Pdk\Carrier\Model\Carrier::CARRIER_POSTNL_NAME,
        ],
    ];

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Throwable
     */
    public function executeInstall(): bool
    {
        $this->installSuccess = 1;

        DefaultLogger::debug('Installing module');

        $this->migrateUp();
        $this->registerHooks();
        $this->installTabs();
        //        $this->addDefaultConfigurations();
        // $this->installCarriers();

        //Tools::clearSf2Cache();

        return (bool) $this->installSuccess;
    }

    /**
     * @param  array $configuration
     *
     * @return \Carrier|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addCarrier(array $configuration)
    {
        $name = $configuration['name'];

        /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierConfigurationRepository $repository */
        //        $repository = Pdk::get(PsCarrierConfigurationRepository::class);

        //        $configuration = $repository->createEntity();

        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('carrier');
        $query->where("external_module_name = '$this->name'");
        $query->where("name = '$name'");
        $existingId = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getValue($query) ?: null;

        //        $carrierConfiguration = $repository->findOneBy(['idCarrier' => $carrier->id]);

        $carrier = new Carrier($existingId);

        $carrier->name                 = $name;
        $carrier->is_module            = true;
        $carrier->active               = 1;
        $carrier->deleted              = 0;
        $carrier->need_range           = 1;
        $carrier->shipping_external    = true;
        $carrier->range_behavior       = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method      = Carrier::SHIPPING_METHOD_PRICE;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = sprintf('Delivery by %s', $name);
        }

        $success = $existingId ? $carrier->update() : $carrier->add();

        if (! $success) {
            return false;
        }

        copy(
            sprintf("%s%s/views/images/%s", _PS_MODULE_DIR_, $this->name, $configuration['image']),
            sprintf("%s/%d.jpg", _PS_SHIP_IMG_DIR_, (int) $carrier->id)
        );

        Configuration::updateValue($configuration['configuration_name'], $carrier->id);

        //        $insert = array_map(static function ($item) use ($carrier) {
        //            return ['id_carrier' => $carrier->id, 'name' => $item, 'value' => ''];
        //        }, Constant::CARRIER_CONFIGURATION_FIELDS);
        //
        //        Db::getInstance()
        //            ->insert(Table::TABLE_CARRIER_CONFIGURATION, $insert, false, false, Db::REPLACE);
        //
        //        CarrierConfigurationProvider::updateValue(
        //            (int) $carrier->id,
        //            'carrierType',
        //            $configuration['carrier_type']
        //        );

        return $carrier;
    }

    //    private function addDefaultConfigurations(): void
    //    {
    //        $configs = [
    //            Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME     => '{order.reference}',
    //            Constant::LABEL_SIZE_CONFIGURATION_NAME            => 'a4',
    //            Constant::LABEL_POSITION_CONFIGURATION_NAME        => 1,
    //            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME   => false,
    //            Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME => 1,
    //        ];
    //
    //        foreach ($configs as $key => $value) {
    //            $this->installSuccess &= Configuration::updateValue($key, $value);
    //        }
    //    }

    /**
     * @param  \Carrier $carrier
     *
     * @return bool
     */
    private function addGroups(Carrier $carrier): bool
    {
        $groups         = Arr::pluck(Group::getGroups(Context::getContext()->language->id), 'id_group');
        $existingGroups = Arr::pluck($carrier->getGroups(), 'id_group');

        $newGroups = array_diff($groups, $existingGroups);

        if (empty($newGroups)) {
            DefaultLogger::error(
                'Groups already present in carrier',
                ['carrier' => $carrier->id, 'groups' => $groups]
            );
            return true;
        }

        return $carrier->setGroups($newGroups);
    }

    /**
     * @param  null|string  $existingId
     * @param  \Carrier     $carrier
     * @param  \ObjectModel $objectModel
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addOrUpdateModel(?string $existingId, Carrier $carrier, ObjectModel $objectModel): bool
    {
        if ($existingId) {
            DefaultLogger::error(
                sprintf('%s already present for carrier', get_class($objectModel)),
                ['carrier' => $carrier->id, 'existingId' => $existingId]
            );
            return $objectModel->update();
        }

        DefaultLogger::debug(sprintf('Created %s for carrier', get_class($objectModel)), ['carrier' => $carrier->id]);
        return $objectModel->add();
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addRangePrice(Carrier $carrier): bool
    {
        $existingId = $this->getExistingIdForCarrier($carrier, 'range_price', 'id_range_price');

        $rangePrice             = new RangePrice($existingId);
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';

        return $this->addOrUpdateModel($existingId, $carrier, $rangePrice);
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addRangeWeight(Carrier $carrier): bool
    {
        $existingId = $this->getExistingIdForCarrier($carrier, 'range_weight', 'id_range_weight');

        $rangeWeight             = new RangeWeight($existingId);
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '10000';

        return $this->addOrUpdateModel($existingId, $carrier, $rangeWeight);
    }

    /**
     * @param  \Carrier $carrier
     *
     * @return bool
     */
    private function addZones(Carrier $carrier): bool
    {
        $result = true;
        $zones  = Arr::pluck($carrier->getZones(), 'id_zone');

        foreach (Zone::getZones() as $zone) {
            $logContext = ['zone' => $zone['id_zone'], 'carrier' => $carrier->id];

            if (in_array($zone['id_zone'], $zones, true)) {
                DefaultLogger::notice('Zone already present in carrier', $logContext);
                continue;
            }

            $result &= $carrier->addZone($zone['id_zone']);

            if (! $result) {
                DefaultLogger::error('Failed to add zone to carrier', $logContext);
            }
        }

        return (bool) $result;
    }

    /**
     * Define the tabs this module adds to the admin.
     *
     * @return array[]
     */
    private function getAdminTabsDefinition(): array
    {
        $languages = [];
        $name      = Str::replaceLast('Controller', '', AdminMyParcelNLController::class);

        foreach (Language::getLanguages() as $lang) {
            $languages[$name][$lang['id_lang']] = 'MyParcelNL';
        }

        return [
            [
                'class_name'   => $name,
                'name'         => $languages[$name],
                'parent_class' => 'AdminParentShipping',
            ],
        ];
    }

    /**
     * @param  \Carrier $carrier
     * @param  string   $table
     * @param  string   $key
     *
     * @return null|string
     */
    private function getExistingIdForCarrier(Carrier $carrier, string $table, string $key): ?string
    {
        $rangePriceQuery = (new DbQuery())
            ->select($key)
            ->from($table)
            ->where("id_carrier = '$carrier->id'");

        return Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getValue($rangePriceQuery) ?: null;
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @deprecated
     */
    private function installCarriers(): void
    {
        $carriers = self::$carriers_nl;

        //        if (ModuleService::isBE()) {
        //            $carriers = array_merge($carriers, self::$carriers_be);
        //        }

        $result = 1;

        foreach ($carriers as $item) {
            $carrier = $this->addCarrier($item);
            $result  &= (bool) $carrier;
            $result  &= $this->addZones($carrier);
            $result  &= $this->addGroups($carrier);
            $result  &= $this->addRangeWeight($carrier);
            $result  &= $this->addRangePrice($carrier);

            if (! $result) {
                DefaultLogger::error('Failed to add carrier', ['carrier' => $carrier->id]);
            }
        }

        $this->installSuccess &= $result;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function installTabs(): void
    {
        foreach ($this->getAdminTabsDefinition() as $definition) {
            /** @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
            $tabRepository = $this->get('prestashop.core.admin.tab.repository');

            $tab                 = new Tab();
            $tab->active         = 1;
            $tab->class_name     = $definition['class_name'];
            $tab->module         = $this->name;
            $tab->name           = $definition['name'];
            $tab->wording        = $definition['class_name'];
            $tab->wording_domain = MyParcelNL::TRANSLATION_DOMAIN;
            $tab->id_parent      = (! empty($definition['parent_class'])
                ? (int) $tabRepository->findOneIdByClassName($definition['parent_class'])
                : -1);

            $result = $tab->add();

            if (! $result) {
                $this->_errors[] = sprintf('Failed to install tab: %s', $definition['name']);
                DefaultLogger::error('Failed to install tab', ['tab' => $definition]);
            }

            $this->installSuccess &= $result;
        }
    }

    /**
     * @return void
     */
    private function migrateUp(): void
    {
        /** @var \MyParcelNL\PrestaShop\Database\Migrations $migrations */
        $migrations = Pdk::get(Migrations::class);

        foreach ($migrations->get() as $migration) {
            /** @var \MyParcelNL\PrestaShop\Database\AbstractMigration $class */
            $class  = Pdk::get($migration);
            $result = $class->up();

            if (! $result) {
                $this->_errors[] = sprintf('Failed to execute migration: %s', $migration);
                DefaultLogger::error('[Install] Failed to execute migration', ['migration' => $migration]);
            }

            $this->installSuccess &= $result;
        }
    }

    private function registerHooks(): void
    {
        foreach (ModuleService::getHooks() as $hook) {
            $result = $this->registerHook($hook);

            if (! $result) {
                $this->_errors[] = sprintf('Hook %s could not be registered.', $hook);
                DefaultLogger::error('[Install] Hook could not be registered', ['hook' => $hook]);
            }

            $this->installSuccess &= $result;
        }
    }
}
