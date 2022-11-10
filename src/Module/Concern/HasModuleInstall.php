<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Concern;

use Carrier;
use Configuration;
use Context;
use Db;
use DbQuery;
use Group;
use Language;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Service\CarrierConfigurationProvider;
use MyParcelNL\Sdk\src\Support\Arr;
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
            'configuration_name' => Constant::BPOST_CONFIGURATION_NAME,
            'carrier_type'       => CarrierOptions::CARRIER_BPOST_NAME,
        ],
        [
            'name'               => 'DPD',
            'image'              => 'dpd.jpg',
            'configuration_name' => Constant::DPD_CONFIGURATION_NAME,
            'carrier_type'       => CarrierOptions::CARRIER_DPD_NAME,
        ],
    ];

    private static $carriers_nl = [
        [
            'name'               => 'PostNL',
            'image'              => 'postnl.jpg',
            'configuration_name' => Constant::POSTNL_CONFIGURATION_NAME,
            'carrier_type'       => CarrierOptions::CARRIER_POSTNL_NAME,
        ],
    ];

    /** @var int */
    private $installSuccess = 1;

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function executeInstall(): bool
    {
        $this->migrateUp();
        $this->registerHooks();
        $this->installTabs();
        $this->addDefaultConfigurations();
        $this->installCarriers();

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

        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('carrier');
        $query->where("external_module_name = '$this->name'");
        $query->where("name = '$name'");
        $existingId = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getValue($query) ?: null;

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
            $carrier->delay[$lang['id_lang']] = 'Super fast delivery';
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

        $insert = array_map(static function ($item) use ($carrier) {
            return ['id_carrier' => $carrier->id, 'name' => $item, 'value' => ''];
        }, Constant::CARRIER_CONFIGURATION_FIELDS);

        Db::getInstance()
            ->insert(Table::TABLE_CARRIER_CONFIGURATION, $insert, false, false, Db::REPLACE);

        CarrierConfigurationProvider::updateValue(
            (int) $carrier->id,
            'carrierType',
            $configuration['carrier_type']
        );

        return $carrier;
    }

    private function addDefaultConfigurations(): void
    {
        $configs = [
            Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME     => '{order.reference}',
            Constant::LABEL_SIZE_CONFIGURATION_NAME            => 'a4',
            Constant::LABEL_POSITION_CONFIGURATION_NAME        => 1,
            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME   => false,
            Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME => 1,
        ];

        foreach ($configs as $key => $value) {
            $this->installSuccess &= Configuration::updateValue($key, $value);
        }
    }

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
            DefaultLogger::notice(
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
    private function addOrUpdateModel(?string $existingId, Carrier $carrier, \ObjectModel $objectModel): bool
    {
        if ($existingId) {
            DefaultLogger::notice(
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
            DefaultLogger::debug('Added zone to carrier', $logContext);
        }

        return (bool) $result;
    }

    /**
     * @return array[]
     */
    private function getAdminTabsDefinition(): array
    {
        $languages = [];

        foreach (Language::getLanguages() as $lang) {
            $languages['AdminMyParcelNL'][$lang['id_lang']] = 'MyParcelBE';
        }

        return [
            [
                'class_name'   => 'AdminMyParcelNL',
                'name'         => $languages['AdminMyParcelNL'],
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
     */
    private function installCarriers(): void
    {
        $carriers = self::$carriers_nl;

        if (ModuleService::isBE()) {
            $carriers = array_merge($carriers, self::$carriers_be);
        }

        $result = 1;

        foreach ($carriers as $item) {
            $carrier = $this->addCarrier($item);
            $result  &= (bool) $carrier;
            $result  &= $this->addZones($carrier);
            $result  &= $this->addGroups($carrier);
            $result  &= $this->addRangeWeight($carrier);
            $result  &= $this->addRangePrice($carrier);
        }

        $this->installSuccess = $result;
    }

    /**
     * @param  array $newTab
     *
     * @return void
     */
    private function installTab(array $newTab): void
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = $newTab['class_name'];
        $tab->name       = $newTab['name'];
        $tab->id_parent  = (! empty($newTab['parent_class'])
            ? (int) Tab::getIdFromClassName($newTab['parent_class'])
            : -1);
        $tab->module     = $this->name;

        $this->installSuccess &= $tab->add();
    }

    private function installTabs(): void
    {
        foreach ($this->getAdminTabsDefinition() as $tab) {
            $this->installTab($tab);
        }
    }

    private function migrateUp(): void
    {
        foreach ($this->migrations as $migration) {
            $result = $migration::up();

            if (! $result) {
                $this->_errors[] = sprintf('Failed to execute migration: %s', $migration);
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
            }

            $this->installSuccess &= $result;
        }
    }
}
