<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Configuration;

use MyParcelNL\PrestaShop\Module\Configuration\Form\ApiForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\CarriersForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\CheckoutForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\CustomsForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\GeneralForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\LabelForm;
use MyParcelNL\PrestaShop\Module\Configuration\Form\OrderForm;
use MyParcelNL\PrestaShop\Module\Tools\Tools as ToolsAlias;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Support\Collection;
use Tools;

class SettingsMenu
{
    private const MENU_API_SETTINGS_NAME      = 'api_settings';
    private const MENU_CARRIER_SETTINGS_NAME  = 'carrier_settings';
    private const MENU_CHECKOUT_SETTINGS_NAME = 'checkout_settings';
    private const MENU_CUSTOMS_SETTINGS_NAME  = 'customs_settings';
    private const MENU_GENERAL_SETTINGS_NAME  = 'general_settings';
    private const MENU_LABEL_SETTINGS_NAME    = 'label_settings';
    private const MENU_ORDER_SETTINGS_NAME    = 'order_settings';

    /** @var \MyParcelNL */
    private $module;

    /**
     * @param  \MyParcelNL $module
     */
    public function __construct(MyParcelNL $module)
    {
        $this->module = $module;
    }

    /**
     * @return array[]
     */
    public function initNavigation(): array
    {
        return $this->getMenuData()
            ->map(function (SettingsMenuItem $menuItem, $index) {
                return [
                    'short'  => $this->module->l($menuItem->getTitle(), 'configure'),
                    'desc'   => $this->module->l($menuItem->getDescription(), 'configure'),
                    'href'   => ToolsAlias::appendQuery($this->module->baseUrl, ['menu' => $index]),
                    'active' => (int) Tools::getValue('menu') === $index,
                    'icon'   => sprintf('icon-%s', $menuItem->getIcon()),
                ];
            })
            ->toArray();
    }

    /**
     * @param  int $formId
     *
     * @return string
     */
    public function renderMenu(int $formId): string
    {
        /** @var \MyParcelNL\PrestaShop\Module\Configuration\SettingsMenuItem $menuItem */
        $menuItem = $this->getMenuData()[$formId];
        $class    = $menuItem->getForm();

        DefaultLogger::debug('Rendering menu', compact('class'));
        return Pdk::get($class)->render();
    }

    /**
     * @return array[]
     */

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection|\MyParcelNL\PrestaShop\Module\Configuration\SettingsMenuItem[]
     */
    private function getMenuData(): Collection
    {
        return (new Collection([
            [
                'name'        => self::MENU_API_SETTINGS_NAME,
                'title'       => 'API',
                'description' => 'API Settings',
                'icon'        => 'gears',
                'form'        => ApiForm::class,
            ],
            [
                'name'        => self::MENU_GENERAL_SETTINGS_NAME,
                'title'       => 'General settings',
                'description' => 'General module settings',
                'icon'        => 'shopping-cart',
                'form'        => GeneralForm::class,
            ],
            [
                'name'  => self::MENU_LABEL_SETTINGS_NAME,
                'title' => 'Label Settings',
                'icon'  => 'shopping-cart',
                'form'  => LabelForm::class,
            ],
            [
                'name'  => self::MENU_ORDER_SETTINGS_NAME,
                'title' => 'Order Settings',
                'icon'  => 'shopping-cart',
                'form'  => OrderForm::class,
            ],
            [
                'name'  => self::MENU_CUSTOMS_SETTINGS_NAME,
                'title' => 'Customs Settings',
                'icon'  => 'shopping-cart',
                'form'  => CustomsForm::class,
            ],
            [
                'name'  => self::MENU_CHECKOUT_SETTINGS_NAME,
                'title' => 'Checkout Settings',
                'icon'  => 'shopping-cart',
                'form'  => CheckoutForm::class,
            ],
            [
                'name'  => self::MENU_CARRIER_SETTINGS_NAME,
                'title' => 'Carrier Settings',
                'icon'  => 'bus',
                'form'  => CarriersForm::class,
            ],
        ]))->mapInto(SettingsMenuItem::class);
    }
}
