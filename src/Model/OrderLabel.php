<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\Entity\OrderStatus\AbstractOrderStatusUpdate;
use Gett\MyparcelBE\Factory\OrderStatus\OrderStatusUpdateCollectionFactory;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Service\MyparcelStatusProvider;
use Gett\MyparcelBE\Service\Tracktrace;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Collection;

/**
 * Not namespaced because PrestaShop 1.6 does not support namespaced ObjectModels.
 *
 * @see          https://devdocs.prestashop.com/1.7/modules/core-updates/1.6/
 * @noinspection PhpIllegalPsrClassPathInspection
 */
class OrderLabel extends ObjectModel
{
    /**
     * @var string|null
     */
    public $id_order;

    /**
     * @var mixed
     */
    public $status;

    /**
     * @var int
     */
    public $new_order_state;

    /**
     * @var string
     */
    public $barcode;

    /**
     * @var string
     */
    public $track_link;

    /**
     * @var int|null
     */
    public $id_label;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    /**
     * @var string
     */
    public $payment_url;

    /**
     * {@inheritdoc}
     */
    public static $definition = [
        'table'     => Table::TABLE_ORDER_LABEL,
        'primary'   => 'id_order_label',
        'multilang' => false,
        'fields'    => [
            'id_order'        => ['type' => self::TYPE_INT, 'required' => true],
            'status'          => ['type' => self::TYPE_STRING],
            'new_order_state' => ['type' => self::TYPE_INT],
            'barcode'         => ['type' => self::TYPE_STRING],
            'track_link'      => ['type' => self::TYPE_STRING],
            'payment_url'     => ['type' => self::TYPE_STRING],
            'id_label'        => ['type' => self::TYPE_STRING],
            'date_add'        => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'        => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * @param  int $shipmentId
     * @param  int $shipmentStatus
     *
     * @throws \Exception
     */
    public static function updateStatus(int $shipmentId, int $shipmentStatus): void
    {
        $statusUpdateCollection = OrderStatusUpdateCollectionFactory::create($shipmentId, $shipmentStatus);
        $statusUpdateCollection->map(static function (AbstractOrderStatusUpdate $statusUpdate) {
            $statusUpdate->onExecute();
        });
    }

    /**
     * @param  int $shipmentId
     *
     * @return \OrderLabel
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function findByLabelId(int $shipmentId): OrderLabel
    {
        $table = Table::withPrefix(self::$definition['table']);
        $id    = Db::getInstance()
            ->getValue(
                <<<SQL
SELECT id_order_label FROM $table where id_label = $shipmentId
SQL
            );

        return new OrderLabel($id);
    }

    /**
     * @param  int $shipmentId
     *
     * @throws \Exception
     */
    public static function sendShippedNotification(int $shipmentId): void
    {
        if (! Configuration::get(Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME)) {
            return;
        }

        $order_label = self::findByLabelId($shipmentId);
        $order       = new Order($order_label->id_order);
        if (! Validate::isLoadedObject($order_label) || ! Validate::isLoadedObject($order)) {
            return;
        }

        $customer = new Customer($order->id_customer);
        if (! Validate::isEmail($customer->email)) {
            return;
        }

        $address         = new Address($order->id_address_delivery);
        $deliveryOptions = DeliveryOptions::getFromOrder($order);

        if (! $deliveryOptions) {
            throw new Exception('Delivery options are missing');
        }

        /** @deprecated use $deliveryOptions */
        $oldDeliveryOptions = DeliveryOptions::queryByOrder($order);
        $oldDeliveryOptions = \Gett\MyparcelBE\Module\Tools\Tools::arrayToObject($oldDeliveryOptions);

        $orderIso           = Language::getIsoById($order->id_lang);
        $templateVars       = [
            '{firstname}'       => $address->firstname,
            '{lastname}'        => $address->lastname,
            '{shipping_number}' => $order_label->barcode,
            '{followup}'        => $order_label->track_link,
            '{order_name}'      => $order->getUniqReference(),
            '{order_id}'        => $order->id,
            '{utc_offset}'      => date('P'),
        ];

        $trackTraceInfo = self::getTrackTraceInfo($order_label);

        $templateVars['{delivery_street}']   = $trackTraceInfo['recipient']['street'];
        $templateVars['{delivery_number}']   = $trackTraceInfo['recipient']['street_additional_info'] . ' ' . $trackTraceInfo['recipient']['number'];
        $templateVars['{delivery_postcode}'] = $trackTraceInfo['recipient']['postal_code'];
        $templateVars['{delivery_city}']     = $trackTraceInfo['recipient']['city'];
        $templateVars['{delivery_cc}']       = $trackTraceInfo['recipient']['cc'];

        $deliveryDate     = $trackTraceInfo['delivery_moment']['start']['date'] ?? $trackTraceInfo['options']['delivery_date'] ?? $deliveryOptions->getDate();
        $deliveryDateFrom = $trackTraceInfo['delivery_moment']['start']['date'] ?? $deliveryOptions->getDate();
        $deliveryDateTo   = $trackTraceInfo['delivery_moment']['end']['date'] ?? $deliveryOptions->getDate();
        $monthNumber      = (int) date('n', strtotime($deliveryDate));

        if ($deliveryOptions->isPickup()) {
            if ($trackTraceInfo['pickup']) {
                $templateVars['{pickup_name}']     = $trackTraceInfo['pickup']['location_name'];
                $templateVars['{pickup_street}']   = $trackTraceInfo['pickup']['street'];
                $templateVars['{pickup_number}']   = $trackTraceInfo['pickup']['number'];
                $templateVars['{pickup_postcode}'] = strtoupper(
                    str_replace(' ', '', $trackTraceInfo['pickup']['postal_code'])
                );
                $templateVars['{pickup_region}']   = $trackTraceInfo['pickup']['region'] ?: '-';
                $templateVars['{pickup_city}']     = $trackTraceInfo['pickup']['city'];
            }

            $templateVars['{pickup_cc}'] = $trackTraceInfo['recipient']['cc'];

            if ('nl' === $orderIso) {
                $dayNumber                                      = (int) date('w', strtotime($deliveryDateFrom));
                $templateVars['{delivery_day_name}']            = Constant::NL_DAYS[$dayNumber];
                $templateVars['{delivery_day}']                 = date('j', strtotime($deliveryDateFrom));
                $templateVars['{delivery_day_leading_zero}']    = date('d', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month}']               = date('n', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month_leading_zero}']  = date('m', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month_name}']          = Constant::NL_MONTHS[$monthNumber];
                $templateVars['{delivery_year}']                = date('Y', strtotime($deliveryDateFrom));
                $templateVars['{delivery_time_from}']           = '15:00';
                $templateVars['{delivery_time_from_localized}'] = '15:00';
            } else {
                $templateVars['{delivery_day_name}']            = date('l', strtotime($deliveryDateFrom));
                $templateVars['{delivery_day}']                 = date('d', strtotime($deliveryDateFrom));
                $templateVars['{delivery_day_leading_zero}']    = date('d', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month}']               = date('m', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month_leading_zero}']  = date('m', strtotime($deliveryDateFrom));
                $templateVars['{delivery_month_name}']          = date('F', strtotime($deliveryDateFrom));
                $templateVars['{delivery_year}']                = date('Y', strtotime($deliveryDateFrom));
                $templateVars['{delivery_time_from}']           = '15:00';
                $templateVars['{delivery_time_from_localized}'] = '03:00 PM';
            }

            foreach (Constant::WEEK_DAYS as $day) {
                $dayFrom = $oldDeliveryOptions->opening_hours->{$day}[0];
                if (false !== strpos($dayFrom, '-')) {
                    [$dayFrom] = explode('-', $dayFrom);
                }
                $dayTo = $oldDeliveryOptions->opening_hours->{$day}[count($oldDeliveryOptions->opening_hours->{$day}) - 1];
                if (false !== strpos($dayTo, '-')) {
                    [, $dayTo] = array_pad(explode('-', $dayTo), 2, '');
                }
                if ($dayFrom) {
                    $dayFull = "$dayFrom - $dayTo";
                } else {
                    $dayFull = Translate::getModuleTranslation('myparcelbe', 'Closed', 'myparcelbe');
                }
                $templateVars["{opening_hours_{$day}_from}"] = $dayFrom;
                $templateVars["{opening_hours_{$day}_to}"]   = $dayTo;
                $templateVars["{opening_hours_{$day}}"]      = $dayFull;
            }
        } else {
            $templateVars['{delivery_day_name}']            = date('l', strtotime($deliveryDateFrom));
            $templateVars['{delivery_day}']                 = date('j', strtotime($deliveryDateFrom));
            $templateVars['{delivery_day_leading_zero}']    = date('d', strtotime($deliveryDateFrom));
            $templateVars['{delivery_month}']               = date('n', strtotime($deliveryDateFrom));
            $templateVars['{delivery_month_leading_zero}']  = date('m', strtotime($deliveryDateFrom));
            $templateVars['{delivery_month_name}']          = date('F', strtotime($deliveryDateFrom));
            $templateVars['{delivery_year}']                = date('Y', strtotime($deliveryDateFrom));
            $templateVars['{delivery_time_from}']           = date('H:i', strtotime($deliveryDateFrom));
            $templateVars['{delivery_time_from_localized}'] = date('h:i A', strtotime($deliveryDateFrom));
            $templateVars['{delivery_time_to}']             = date('H:i', strtotime($deliveryDateTo));
            $templateVars['{delivery_time_to_localized}']   = date('h:i A', strtotime($deliveryDateTo));
        }

        $mailType = self::getMailType($deliveryOptions);
        $mailDir  = self::getMailDir($orderIso, $mailType);

        if ($mailDir) {
            Mail::send(
                $order->id_lang,
                "myparcel_{$mailType}_shipped",
                'nl' === $orderIso
                    ? "Bestelling {$order->getUniqReference()} is verzonden"
                    : "Order {$order->getUniqReference()} has been shipped",
                $templateVars,
                $customer->email,
                null,
                (string) Configuration::get(
                    'PS_SHOP_EMAIL',
                    null,
                    null,
                    Context::getContext()->shop->id
                ),
                (string) Configuration::get(
                    'PS_SHOP_NAME',
                    null,
                    null,
                    Context::getContext()->shop->id
                ),
                null,
                null,
                $mailDir,
                false,
                Context::getContext()->shop->id
            );
        }
    }

    /**
     * @param  int $shipmentId
     * @param  int $newOrderStatus
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function setOrderStatus(int $shipmentId, int $newOrderStatus): void
    {
        $orderLabel     = self::findByLabelId($shipmentId);
        $order          = new Order($orderLabel->id_order);
        $oldOrderStatus = $order->getCurrentState();

        if (! self::validateSetOrderStatus($orderLabel, $order, $newOrderStatus)) {
            return;
        }

        $order->setCurrentState($newOrderStatus);
        $order->save();
        ApiLogger::addLog(
            sprintf(
                'Order %d status changed from %d to %d',
                $orderLabel->id_order,
                $oldOrderStatus,
                $newOrderStatus
            )
        );
    }

    /**
     * @param  array $orderIds
     *
     * @return array|bool|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     */
    public static function getDataForLabelsCreate(array $orderIds)
    {
        $qb = new DbQuery();
        $qb->select('orders.id_order,
                    orders.id_order AS id,
                    orders.reference,
                    country.iso_code,
                    state.name AS state_name,
                    CONCAT(address.firstname, " ",address.lastname) AS person,
                    CONCAT(address.address1, " ", address.address2) AS full_street,
                    address.postcode,
                    address.city,
                    address.company,
                    customer.email,
                    address.phone,
                    delivery_settings.delivery_settings,
                    orders.id_carrier,
                    address.id_country,
                    orders.invoice_number,
                    orders.shipping_number
                    ');

        $qb->from('orders', 'orders');
        $qb->innerJoin('address', 'address', 'orders.id_address_delivery = address.id_address');
        $qb->innerJoin('country', 'country', 'country.id_country = address.id_country');
        $qb->innerJoin('customer', 'customer', 'orders.id_customer = customer.id_customer');
        $qb->leftJoin('state', 'state', 'state.id_state = address.id_state');
        $qb->leftJoin(Table::TABLE_DELIVERY_SETTINGS, 'delivery_settings', 'orders.id_cart = delivery_settings.id_cart');

        $qb->where('id_order IN (' . implode(',', $orderIds) . ') ');

        return Db::getInstance()->executeS($qb);
    }

    /**
     * @param  int $id_order
     *
     * @return array|bool|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     */
    public static function getOrderProducts(int $id_order)
    {
        $qb = new DbQuery();
        $qb->select('od.product_id');
        $qb->from('order_detail', 'od');
        $qb->where('od.id_order = "' . $id_order . '" ');

        return Db::getInstance()->executeS($qb);
    }

    /**
     * @param  array $orders_id
     *
     * @return array
     */
    public static function getOrdersLabels(array $orders_id): array
    {
        $qb = new DbQuery();
        $qb->select('ol.id_label');
        $qb->from(Table::TABLE_ORDER_LABEL, 'ol');
        $qb->where('ol.id_order IN (' . implode(',', $orders_id) . ') ');

        $return = [];
        foreach (Db::getInstance()->executeS($qb) as $item) {
            $return[] = $item['id_label'];
        }

        return $return;
    }

    /**
     * @param  int   $order_id
     * @param  array $label_ids
     *
     * @return array|bool|\mysqli_result|\PDOStatement|resource|null
     * @throws \PrestaShopDatabaseException
     */
    public static function getOrderLabels(int $order_id, array $label_ids = [])
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(Table::TABLE_ORDER_LABEL);
        $sql->where('id_order = ' . (int) $order_id);
        if (!empty($label_ids)) {
            $sql->where('id_label IN(' . implode(',', $label_ids) . ')');
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param  int $id_order
     *
     * @return array|\mysqli_result|\PDOStatement|resource
     * @throws \PrestaShopDatabaseException
     */
    public static function getCustomsOrderProducts(int $id_order)
    {
        $qb = new DbQuery();
        $qb->select('od.product_id, pc.value , od.product_quantity, od.product_name, od.product_weight');
        $qb->select('od.unit_price_tax_incl');
        $qb->from('order_detail', 'od');
        $qb->leftJoin(Table::TABLE_PRODUCT_CONFIGURATION, 'pc', 'od.product_id = pc.id_product');
        $qb->where('od.id_order = ' . $id_order);
        $qb->where('pc.name = "' . Constant::CUSTOMS_FORM_CONFIGURATION_NAME . '"');
        $qb->where('pc.value = "' . Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_ADD . '"');

        return Db::getInstance()->executeS($qb) ?? [];
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     * @param  \Gett\MyparcelBE\Service\MyparcelStatusProvider           $status_provider
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function createFromConsignment(
        AbstractConsignment $consignment,
        MyparcelStatusProvider $status_provider
    ): int {
        $orderLabel = new self();
        $orderLabel->id_label = $consignment->getConsignmentId();
        $orderLabel->id_order = $consignment->getReferenceId();
        $orderLabel->barcode = $consignment->getBarcode();
        $orderLabel->track_link = $consignment->getBarcodeUrl(
            $consignment->getBarcode(),
            $consignment->getPostalCode(),
            $consignment->getCountry()
        );
        $orderLabel->new_order_state = $consignment->getStatus();
        $orderLabel->status = $status_provider->getStatus($consignment->getStatus());
        if ($orderLabel->add()) {
            return (int) $orderLabel->id_label;
        }

        return 0;
    }

    /**
     * @param  int $labelId
     *
     * @return int
     */
    public static function getOrderIdByLabelId(int $labelId): int
    {
        $sql = new DbQuery();
        $sql->select('id_order');
        $sql->from(Table::TABLE_ORDER_LABEL);
        $sql->where('id_label = ' . (int) $labelId);

        return (int) Db::getInstance()->getValue($sql);
    }

    /**
     * @param  int|string $orderId
     * @param  string     $tracktrace
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function updateOrderTrackingNumber($orderId, string $tracktrace): bool
    {
        $order = new Order((int) $orderId);

        if (! Validate::isLoadedObject($order)) {
            return false;
        }

        $orderCarrierId = $order->getIdOrderCarrier();
        $orderCarrier   = new OrderCarrier($orderCarrierId);

        if (! Validate::isTrackingNumber($tracktrace)) {
            return false;
        }

        $order->shipping_number = $tracktrace;
        $order->update();

        if (Validate::isLoadedObject($orderCarrier)) {
            $orderCarrier->tracking_number = pSQL($tracktrace);
            return $orderCarrier->update();
        }

        return false;
    }

    /**
     * @param  string $mailIso
     * @param  string $mailType
     *
     * @return string|null
     */
    protected static function getMailDir(string $mailIso, string $mailType): ?string
    {
        $themeDir  = sprintf('%smodules/%s/mails/', _PS_THEME_DIR_, MyParcelBE::MODULE_NAME);
        $moduleDir = sprintf('%s%s/mails/', _PS_MODULE_DIR_, MyParcelBE::MODULE_NAME);
        $dirs      = new Collection([$themeDir, $moduleDir]);
        $languages = new Collection([$mailIso, Constant::MAIL_FALLBACK_LANGUAGE]);

        foreach ($languages as $language) {
            $mailDir = $dirs->first(static function ($dir) use ($language, $mailType) {
                return file_exists("$dir$language/myparcel_{$mailType}_shipped.txt")
                    && file_exists("$dir$language/myparcel_{$mailType}_shipped.html");
            });
        }

        return $mailDir ?? null;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return string
     */
    protected static function getMailType(AbstractDeliveryOptionsAdapter $deliveryOptions): string
    {
        $mailType = (AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME === $deliveryOptions->getPackageType())
            ? 'mailboxpackage' : 'standard';

        if ($deliveryOptions->isPickup()) {
            $mailType = 'pickup';
        }
        return $mailType;
    }

    /**
     * @param  \OrderLabel $orderLabel
     *
     * @return array|null
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    protected static function getTrackTraceInfo(OrderLabel $orderLabel): ?array
    {
        $trackTraceInfo = null;

        try {
            $trackTraceInfo = (new Tracktrace(Configuration::get(Constant::API_KEY_CONFIGURATION_NAME)))->getTrackTrace(
                $orderLabel->id_label,
                true
            );
        } catch (ApiException $e) {
            Logger::addLog($e->getMessage(), true);
        }

        return $trackTraceInfo['data']['tracktraces'][0] ?? null;
    }

    /**
     * @param  \OrderLabel $orderLabel
     * @param  \Order                             $order
     * @param  int                                $newOrderStatus
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    protected static function validateSetOrderStatus(OrderLabel $orderLabel, Order $order, int $newOrderStatus): bool
    {
        if (! Validate::isLoadedObject($orderLabel) || ! Validate::isLoadedObject($order)) {
            ApiLogger::addLog('No order found for given shipment id.', ApiLogger::ERROR);
            return false;
        }

        if (self::orderStatusShouldBeIgnored($order)) {
            ApiLogger::addLog('Current order status is ignored.');
            return false;
        }

        if (self::orderHistoryContainsStatus($order, $newOrderStatus)) {
            ApiLogger::addLog('New order status is already present in order history.');
            return false;
        }

        return true;
    }

    /**
     * @param  \Order $order
     * @param  int    $status
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    private static function orderHistoryContainsStatus(Order $order, int $status): bool
    {
        $table   = Table::withPrefix('order_history');
        $history = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS(
                <<<SQL
SELECT `id_order_state` FROM $table WHERE `id_order` = $order->id 
SQL
            );

        if (is_array($history)) {
            $statuses = array_column($history, 'id_order_state');

            if (in_array((string) $status, $statuses, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \Order $order
     *
     * @return bool
     */
    private static function orderStatusShouldBeIgnored(Order $order): bool
    {
        $ignore = Configuration::get(Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME);

        if ($ignore) {
            $ignore = explode(',', $ignore);

            if (in_array($order->getCurrentState(), $ignore, true)) {
                return true;
            }
        }

        return false;
    }
}
