<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Address;
use AddressFormat;
use Configuration;
use Context;
use Currency;
use Customer;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliverySettings\DeliverySettings;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Module\Carrier\Provider\DeliveryOptionsProvider;
use Gett\MyparcelBE\Provider\OrderLabelProvider;
use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use Module;
use Validate;

class AdminOrderView extends AbstractAdminOrder
{
    /**
     * @var \MyParcelBE
     */
    private $module;

    /**
     * @var int
     */
    private $idOrder;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Gett\MyparcelBE\Model\Core\Order
     */
    private $order;

    public function __construct(Module $module, int $idOrder, Context $context = null)
    {
        $this->module = $module;
        $this->idOrder = $idOrder;
        $this->context = $context ?? Context::getContext();
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     * @throws \SmartyException
     * @throws \Exception
     */
    public function display(): string
    {
        $order = $this->getOrder();

        if (! Validate::isLoadedObject($order)) {
            return '';
        }

        $psVersion = '';
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $psVersion = '-177';
        }

        $currency = Currency::getDefaultCurrency();
        $weight   = (new OrderTotalWeight())->convertWeightToGrams($order->getTotalWeight());

        $link                     = $this->context->link;
        $labelUrl                 = $link->getAdminLink('AdminMyParcelBELabel', true, [], ['id_order' => $order->getId()]);
        $deliveryAddress          = new Address($order->id_address_delivery);
        $deliveryAddressFormatted = AddressFormat::generateAddress($deliveryAddress, [], '<br />');
        $bulk_actions             = [
            'refreshLabels' => [
                'text' => $this->module->l('Refresh', 'adminorderview'),
                'icon' => 'icon-refresh',
                'ajax' => 1,
            ],
            'printLabels'   => [
                'text' => $this->module->l('Print', 'adminorderview'),
                'icon' => 'icon-print',
            ],
        ];

        $extraOptions = DeliverySettings::getExtraOptionsFromOrder($order);
        if (! $extraOptions->getDigitalStampWeight()) {
            $extraOptions->setDigitalStampWeight($weight);
        }

        $deliveryOptionsProvider  = new DeliveryOptionsProvider();
        $deliveryOptions          = $deliveryOptionsProvider->provide($order->getId());
        $labelList                = $this->getLabels();

        $labelListHtml = $this->context->smarty->createData($this->context->smarty);
        $labelListHtml->assign([
            'labelList'              => $labelList,
            'promptForLabelPosition' => Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
        ]);
        $labelListHtmlTpl = $this->context->smarty->createTemplate(
            $this->module->getTemplatePath('views/templates/admin/hook/label-list' . $psVersion . '.tpl'),
            $labelListHtml
        );

        $labelConceptHtml = $this->context->smarty->createData($this->context->smarty);
        $labelReturnHtml  = $this->context->smarty->createData($this->context->smarty);

        $carrierSettingsProvider = new CarrierSettingsProvider($this->module);
        $deliveryAddress         = new Address($order->id_address_delivery);
        $customer                = new Customer($order->id_customer);

        $carrierSettings = $carrierSettingsProvider->provide($order->getIdCarrier());
        $currencySign    = $currency->getSign();

        $common = [
            'currencySign'           => $currencySign,
            'deliveryOptions'        => $deliveryOptions,
            'extraOptions'           => $extraOptions->toArray(),
            'weight'                 => $weight,
        ];

        $labelConceptHtml->assign(
            array_merge($common, [
                'carrierSettings'      => $carrierSettings,
                'date_warning_display' => $deliveryOptionsProvider->provideWarningDisplay($order->getId()),
                'isBE'                 => $this->module->isBE(),
            ])
        );

        $labelReturnHtml->assign(
            array_merge($common, [
                'carrierSettings' => $carrierSettings,
                'customerEmail'   => $customer->email,
                'customerName'    => trim($deliveryAddress->firstname . ' ' . $deliveryAddress->lastname),
                'isBE'            => $this->module->isBE(),
                'labelUrl'        => $labelUrl,
            ])
        );

        $labelConceptHtmlTpl = $this->context->smarty->createTemplate(
            $this->module->getTemplatePath('views/templates/admin/hook/label-concept' . $psVersion . '.tpl'),
            $labelConceptHtml
        );

        $labelReturnHtmlTpl = $this->context->smarty->createTemplate(
            $this->module->getTemplatePath('views/templates/admin/hook/label-return-form' . $psVersion . '.tpl'),
            $labelReturnHtml
        );

        $this->context->smarty->assign(array_merge($common, [
            'modulePathUri'              => $this->module->getPathUri(),
            'id_order'                   => $order->getId(),
            'id_carrier'                 => $order->getIdCarrier(),
            'addressEditUrl'             => $link->getAdminLink('AdminAddresses', true, [], [
                'id_order'     => $order->getId(),
                'id_address'   => $order->id_address_delivery,
                'addaddress'   => '',
                'realedit'     => 1,
                'address_type' => 1,
                'back'         => urlencode(str_replace('&conf=4', '', $_SERVER['REQUEST_URI'])),
            ]),
            'delivery_address_formatted' => $deliveryAddressFormatted,
            'labelListHtml'              => $labelListHtmlTpl->fetch(),
            'labelConceptHtml'           => $labelConceptHtmlTpl->fetch(),
            'labelReturnHtml'            => $labelReturnHtmlTpl->fetch(),
            'labelList'                  => $labelList,
            'bulk_actions'               => $bulk_actions,
            'labelUrl'                   => $labelUrl,
            'labelAction'                => $link->getAdminLink(
                'AdminMyParcelBELabel',
                true,
                [],
                ['action' => 'createLabel']
            ),
            'download_action'            => $link->getAdminLink(
                'AdminMyParcelBELabel',
                true,
                [],
                ['action' => 'downloadLabel']
            ),
            'print_bulk_action'          => $link->getAdminLink('AdminMyParcelBELabel', true, [], ['action' => 'print']
            ),
            'export_print_bulk_action'   => $link->getAdminLink(
                'AdminMyParcelBELabel',
                true,
                [],
                ['action' => 'exportPrint']
            ),
            'carrierLabels'              => Constant::SINGLE_LABEL_CREATION_OPTIONS,
            'labelConfiguration'         => $this->getLabelDefaultConfiguration(),
            'promptForLabelPosition'     => Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
        ]));

        return $this->module->display(
            $this->module->name,
            'views/templates/admin/hook/order-label-block' . $psVersion . '.tpl'
        );
    }

    public function getLabels()
    {
        return (new OrderLabelProvider($this->module))->provideLabels($this->idOrder, []);
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        try {
            return $this->getOrder()->getTotalWeight();
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    private function getOrder(): Order
    {
        if (! $this->order) {
            $this->order = new Order($this->idOrder);
        }

        return $this->order;
    }
}
