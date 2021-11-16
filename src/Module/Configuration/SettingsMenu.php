<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Configuration;

use Gett\MyparcelBE\Module\Configuration\Form\ApiForm;
use Gett\MyparcelBE\Module\Configuration\Form\CarriersForm;
use Gett\MyparcelBE\Module\Configuration\Form\CheckoutForm;
use Gett\MyparcelBE\Module\Configuration\Form\CustomsForm;
use Gett\MyparcelBE\Module\Configuration\Form\GeneralForm;
use Gett\MyparcelBE\Module\Configuration\Form\LabelForm;
use Gett\MyparcelBE\Module\Configuration\Form\OrderForm;
use Module;
use MyParcelBE;
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

    /** @var Module */
    private $module;

    /**
     * @param  \MyParcelBE $module
     */
    public function __construct(MyParcelBE $module)
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
                    'href'   => $this->module->appendQueryToUrl($this->module->baseUrl, ['menu' => $index]),
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
        /** @var \Gett\MyparcelBE\Module\Configuration\SettingsMenuItem */
        $menuItem = $this->getMenuData()[$formId];
        $class    = $menuItem->getForm();

        /** @var \Gett\MyparcelBE\Module\Configuration\Form\AbstractForm */
        $form = new $class($this->module);

        return $form->render();
    }

    /**
     * @return array[]
     */

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection|\Gett\MyparcelBE\Module\Configuration\SettingsMenuItem[]
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
