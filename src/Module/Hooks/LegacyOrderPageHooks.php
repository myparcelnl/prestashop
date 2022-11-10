<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Configuration;
use DateTime;
use Dispatcher;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\DeliveryOptions\DeliveryOptionsManager;
use MyParcelNL\PrestaShop\Label\LabelOptionsResolver;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Module\Hooks\Helpers\AdminOrderView;
use MyParcelNL\PrestaShop\Pdk\Facade\OrderLogger;
use MyParcelNL\PrestaShop\Service\CarrierService;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use Throwable;
use Validate;

trait LegacyOrderPageHooks
{
    protected $carrierList = [];

    public function hookActionAdminOrdersListingFieldsModifier(&$params)
    {
        if (! isset($params['select'])) {
            $params['select'] = '';
        }
        if (! isset($params['join'])) {
            $params['join'] = '';
        }
        $prefix = 'car' . $this->id;

        $params['select'] .= ', 1 as `myparcel_void_1`, 1 as `myparcel_void_2`, a.`id_carrier`';
        $params['select'] .= ', a.`id_cart` , \'\' AS myparcel_void_0';
        $params['select'] .= ', ' . $prefix . '.`id_reference` AS id_carrier_reference';
        $params['select'] .= ', ' . $prefix . '.`name` AS carrier_name';

        $params['join'] .= '
            LEFT JOIN ' . Table::withPrefix(
                'carrier'
            ) . ' AS ' . $prefix . ' ON (a.id_carrier = ' . $prefix . '.id_carrier)';

        $params['fields']['myparcel_void_0'] = [
            'title'           => $this->l('Delivery date', 'legacyorderpagehooks'),
            'callback'        => 'printMyParcelDeliveryInfo',
            'search'          => false,
            'orderby'         => false,
            'callback_object' => $this,
        ];

        $params['fields']['myparcel_void_1'] = [
            'title'           => $this->l('Labels', 'legacyorderpagehooks'),
            'class'           => 'pointer-myparcel-labels text-center',
            'callback'        => 'printMyParcelLabel',
            'search'          => false,
            'orderby'         => false,
            'remove_onclick'  => true,
            'callback_object' => $this,
        ];

        $params['fields']['myparcel_void_2'] = [
            'title'           => '',
            'class'           => 'text-nowrap',
            'callback'        => 'printMyParcelIcon',
            'search'          => false,
            'orderby'         => false,
            'remove_onclick'  => true,
            'callback_object' => $this,
        ];
    }

    /**
     * Before the order list
     *
     * @return string
     */
    public function hookDisplayAdminListBefore(): string
    {
        return '';
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookDisplayInvoice($params): string
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            return '';
        }
        $idOrder    = (int) $params['id_order'];
        $controller = Dispatcher::getInstance()
            ->getController();

        if (empty($idOrder) || $controller !== 'AdminOrders') {
            return '';
        }

        $order = new Order($idOrder);

        if (! Validate::isLoadedObject($order)) {
            return '';
        }

        return (new AdminOrderView((int) $params['id_order']))->display();
    }

    /**
     * @param $id
     * @param $row
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function printMyParcelDeliveryInfo($id, $row)
    {
        if (empty($row['id_carrier_reference'])) {
            return '';
        }

        if (! CarrierService::hasMyParcelCarrier((int) $row['id_carrier_reference'])) {
            return '';
        }

        $deliveryOptions = DeliveryOptionsManager::getFromCart($row['id_cart']);

        if (! $deliveryOptions) {
            return '';
        }

        $deliveryOptionsArray = $deliveryOptions->toArray();

        try {
            if (empty($deliveryOptionsArray['date'])) {
                return '';
            }

            $date          = new DateTime($deliveryOptionsArray['date']);
            $dateFormatted = $date->format($this->context->language->date_format_lite);

            if (! empty($dateFormatted)) {
                $id = sprintf('[%s] %s', $dateFormatted, $row['carrier_name']);
            }
        } catch (Throwable $exception) {
            OrderLogger::debug($exception->getMessage(), compact('exception'));
        }

        return $id;
    }

    //    /**
    //     * @throws \Exception
    //     */
    //    public function hookActionAdminControllerSetMedia(): void
    //    {
    //        if ('AdminOrders' === $this->context->controller->php_self || is_a($this->context->controller, '\AdminOrdersController')) {
    //            $adminOrder = new AdminOrderList();
    //            $adminOrder->setHeaderContent();
    //        }
    //    }

    /**
     * @param $id
     * @param $params
     *
     * @return mixed
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function printMyParcelIcon($id, $params)
    {
        $psCarrierId = (int) $params['id_carrier'];
        $carrier     = CarrierService::getMyParcelCarrier($psCarrierId);

        $labelOptionsResolver = Pdk::get(LabelOptionsResolver::class);
        $order                = new Order((int) $params['id_order']);

        $consignment = ConsignmentFactory::createFromCarrier($carrier);

        $this->context->smarty->assign([
            'label_options'         => $labelOptionsResolver->getLabelOptionsJson($order),
            'allowSetOnlyRecipient' => $consignment->canHaveShipmentOption(
                AbstractConsignment::SHIPMENT_OPTION_ONLY_RECIPIENT
            ),
            'allowSetSignature'     => $consignment->canHaveShipmentOption(
                AbstractConsignment::SHIPMENT_OPTION_SIGNATURE
            ),
        ]);

        return $this->display($this->name, 'views/templates/admin/icon-concept.tpl');
    }

    public function printMyParcelLabel($id, $params)
    {
        if (! $this->searchMyParcelCarrier((int) $params['id_carrier'])) {
            return '';
        }
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from(Table::TABLE_ORDER_LABEL);
        $sql->where('id_order = "' . pSQL($params['id_order']) . '" ');
        $result = \Db::getInstance()
            ->executeS($sql);
        $link   = $this->context->link;

        $this->context->smarty->assign([
            'labels'                 => $result,
            'link'                   => $link,
            'promptForLabelPosition' => Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
        ]);

        return $this->display($this->name, 'views/templates/admin/icon-labels.tpl');
    }

    public function searchMyParcelCarrier($idCarrier)
    {
        $carrier = $this->carrierList[$idCarrier] ?? new \Carrier($idCarrier);
        if (empty($this->carrierList[$idCarrier])) {
            $this->carrierList[$idCarrier] = $carrier->id_reference;
        }

        return in_array($this->carrierList[$idCarrier], [
            Configuration::get(Constant::DPD_CONFIGURATION_NAME),
            Configuration::get(Constant::BPOST_CONFIGURATION_NAME),
            Configuration::get(Constant::POSTNL_CONFIGURATION_NAME),
        ]);
    }
}
