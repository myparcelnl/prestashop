<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Entity\OrderStatus\AbstractOrderStatusUpdate;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Factory\OrderStatus\OrderStatusUpdateCollectionFactory;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\OrderLogger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\MyParcelStatusProvider;
use Gett\MyparcelBE\Service\Tracktrace;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
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
    public static  $definition = [
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

    private static $cache=[];

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
     * @param  int $labelId
     *
     * @return \OrderLabel
     */
    public static function findByLabelId(int $labelId): OrderLabel
    {
        $orderLabels = self::findByLabelIds([$labelId]);
        return $orderLabels[0];
    }

    /**
     * @param  array $labelIds
     *
     * @return \OrderLabel[]
     */
    public static function findByLabelIds(array $labelIds): array
    {
        $orderLabels = [];
        $fetchFromDb = [];

        foreach ($labelIds as $labelId) {
            if (array_key_exists($labelId, self::$cache)) {
                $orderLabels[] = self::$cache[$labelId];
                continue;
            }
            $fetchFromDb[] = $labelId;
        }

        if (! empty($fetchFromDb)) {
            array_push($orderLabels, ...self::getLabelsFromDb($fetchFromDb));
        }

        return $orderLabels;
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

        $orderLabel = self::findByLabelId($shipmentId);
        $order      = new Order($orderLabel->id_order);
        if (! Validate::isLoadedObject($orderLabel) || ! Validate::isLoadedObject($order)) {
            return;
        }

        $customer = new Customer($order->id_customer);
        if (! Validate::isEmail($customer->email)) {
            return;
        }

        $address         = new Address($order->id_address_delivery);
        $deliveryOptions = OrderSettingsFactory::create($order)
            ->getDeliveryOptions();

        if (! $deliveryOptions) {
            throw new Exception('Delivery options are missing');
        }

        /** @deprecated use $deliveryOptions */

        $oldDeliveryOptions = OrderSettingsFactory::create($order)->getDeliveryOptions();
        $oldDeliveryOptions = \Gett\MyparcelBE\Module\Tools\Tools::arrayToObject(
            $oldDeliveryOptions
                ? $oldDeliveryOptions->toArray()
                : []
        );

        $orderIso     = Language::getIsoById($order->id_lang);
        $templateVars = [
            '{firstname}'       => $address->firstname,
            '{lastname}'        => $address->lastname,
            '{shipping_number}' => $orderLabel->barcode,
            '{followup}'        => $orderLabel->track_link,
            '{order_name}'      => $order->getUniqReference(),
            '{order_id}'        => $order->id,
            '{utc_offset}'      => date('P'),
        ];

        $trackTraceInfo = self::getTrackTraceInfo($orderLabel);

        $templateVars['{delivery_street}']   = $trackTraceInfo['recipient']['street'];
        $templateVars['{delivery_number}']   = $trackTraceInfo['recipient']['street_additional_info'] . ' ' . $trackTraceInfo['recipient']['number'];
        $templateVars['{delivery_postcode}'] = $trackTraceInfo['recipient']['postal_code'];
        $templateVars['{delivery_city}']     = $trackTraceInfo['recipient']['city'];
        $templateVars['{delivery_cc}']       = $trackTraceInfo['recipient']['cc'];

        $deliveryDate     = $trackTraceInfo['delivery_moment']['start']['date'] ?? $trackTraceInfo['options']['delivery_date'] ?? $deliveryOptions->getDate(
            );
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
                $dayTo = $oldDeliveryOptions->opening_hours->{$day}[count(
                    $oldDeliveryOptions->opening_hours->{$day}
                ) - 1];
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
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function setOrderStatus(int $shipmentId, int $newOrderStatus): bool
    {
        $orderLabel     = self::findByLabelId($shipmentId);
        $order          = new Order($orderLabel->id_order);
        $oldOrderStatus = $order->getCurrentState();

        if (! self::validateSetOrderStatus($orderLabel, $order, $newOrderStatus)) {
            return false;
        }

        $order->setCurrentState($newOrderStatus);
        $order->save();

        OrderLogger::addLog([
            'message' => sprintf('Status changed from %d to %d', $oldOrderStatus, $newOrderStatus),
            'order'   => $order,
        ]);

        return true;
    }

    /**
     * @param int $orderId
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public static function getDataForLabelsCreate(int $orderId): array
    {
        return \Gett\MyparcelBE\Entity\Cache::remember(
            "data_labels_create_$orderId",
            static function () use ($orderId) {
                $qb = new DbQuery();
                $qb->select(
                    'orders.id_order,
                    orders.id_order AS id,
                    orders.reference,
                    country.iso_code,
                    state.name AS state_name,
                    CONCAT(address.firstname, " ", address.lastname) AS person,
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
                    '
                );

                $qb->from('orders', 'orders');
                $qb->innerJoin('address', 'address', 'orders.id_address_delivery = address.id_address');
                $qb->innerJoin('country', 'country', 'country.id_country = address.id_country');
                $qb->leftJoin('customer', 'customer', 'orders.id_customer = customer.id_customer');
                $qb->leftJoin('state', 'state', 'state.id_state = address.id_state');
                $qb->leftJoin(
                    Table::TABLE_DELIVERY_SETTINGS,
                    'delivery_settings',
                    'orders.id_cart = delivery_settings.id_cart'
                );

                $qb->where("id_order = $orderId");

                $result = Db::getInstance()
                    ->executeS($qb);

                if (! $result) {
                    OrderLogger::addLog([
                        'message' => 'Order data not complete',
                        'order'   => $orderId,
                        'query'   => $qb->build(),
                    ], OrderLogger::WARNING);
                }

                if (! $result[0]['email']) {
                    throw new MissingFieldException(
                        sprintf('Customer not found for order %s', $orderId)
                    );
                }

                return $result[0];
            }
        );
    }

    /**
     * @param  array $orderIds
     *
     * @return int[]
     */
    public static function getOrdersLabels(array $orderIds): array
    {
        $qb = new DbQuery();
        $qb->select('ol.id_label');
        $qb->from(Table::TABLE_ORDER_LABEL, 'ol');
        $qb->where('ol.id_order IN (' . implode(',', $orderIds) . ') ');

        try {
            $resource = Db::getInstance()->executeS($qb);
        } catch (Exception $e) {
            ApiLogger::addLog($e);
            return [];
        }

        return array_map('intval', Arr::pluck($resource, 'id_label'));
    }

    /**
     * @param  int   $orderId
     * @param  array $labelIds
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public static function getOrderLabels(int $orderId, array $labelIds = []): array
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(Table::TABLE_ORDER_LABEL);
        $sql->where("id_order = $orderId");

        if (! empty($labelIds)) {
            $sql->where(sprintf('id_label IN (%s)', implode(',', $labelIds)));
        }

        return Db::getInstance()
            ->executeS($sql) ?: [];
    }

    /**
     * @param  int $id_order
     *
     * @return array|\mysqli_result|\PDOStatement|resource
     * @throws \PrestaShopDatabaseException
     */
    public static function getCustomsOrderProducts(int $id_order)
    {
        // allow a non-existent myparcel customs setting if the default is 'Add'
        $default    = Configuration::get(Constant::CUSTOMS_FORM_CONFIGURATION_NAME);
        $defaultSql = (Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_ADD === $default)
            ? ' OR pc.name IS NULL'
            : '';
        $dbQuery    = new DbQuery();
        $dbQuery->select('od.product_id, pc.value, od.product_quantity, od.product_name, od.product_weight');
        $dbQuery->select('od.unit_price_tax_incl');
        $dbQuery->from('order_detail', 'od');
        $dbQuery->leftJoin(Table::TABLE_PRODUCT_CONFIGURATION, 'pc', 'od.product_id = pc.id_product');
        $dbQuery->where(
            'od.id_order = '
            . $id_order
            . ' AND ((pc.name = "'
            . Constant::CUSTOMS_FORM_CONFIGURATION_NAME
            . '" AND pc.value = "'
            . Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_ADD
            . '")'
            . $defaultSql
            . ')'
        );

        return Db::getInstance()->executeS($dbQuery) ?? [];
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return null|array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function createFromConsignment(AbstractConsignment $consignment): ?array
    {
        $orderLabel = self::create($consignment);
        if ($orderLabel->add()) {
            return [
                'id_order'        => $orderLabel->id_order,
                'id_label'        => $orderLabel->id_label,
                'status'          => $orderLabel->status,
                'new_order_state' => $orderLabel->new_order_state,
                'barcode'         => $orderLabel->barcode,
                'track_link'      => $orderLabel->track_link,
                'date_add'        => $orderLabel->date_add,
                'date_upd'        => $orderLabel->date_upd,
                'payment_url'     => $orderLabel->payment_url,
            ];
        }

        return null;
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
     * @param  \Order $order
     * @param  string $barcode
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function updateOrderTrackingNumber(Order $order, string $barcode): bool
    {
        if (! Validate::isLoadedObject($order)) {
            return false;
        }

        $orderCarrierId = $order->getIdOrderCarrier();
        $orderCarrier   = new OrderCarrier($orderCarrierId);

        if (! Validate::isTrackingNumber($barcode)) {
            return false;
        }

        $order->shipping_number = $barcode;
        $order->update();

        if (Validate::isLoadedObject($orderCarrier)) {
            OrderLogger::addLog([
                'message' => "Updating tracking number to $barcode",
                'order'   => $order,
            ]);
            $orderCarrier->tracking_number = pSQL($barcode);
            return $orderCarrier->update();
        }

        return false;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return \OrderLabel
     */
    protected static function create(AbstractConsignment $consignment): OrderLabel
    {
        $orderLabel                  = new self();
        $orderLabel->id_label        = $consignment->getConsignmentId();
        $orderLabel->id_order        = $consignment->getReferenceIdentifier();
        $orderLabel->barcode         = $consignment->getBarcode();
        $orderLabel->track_link      = $consignment->getBarcodeUrl(
            $consignment->getBarcode(),
            $consignment->getPostalCode(),
            $consignment->getCountry()
        );
        $orderLabel->new_order_state = $consignment->getStatus();
        $orderLabel->status          = MyParcelStatusProvider::getInstance()->getStatus($consignment->getStatus());

        return $orderLabel;
    }

    /**
     * @param  array $labelIds
     *
     * @return array
     */
    protected static function getLabelsFromDb(array $labelIds): array
    {
        $idsString = sprintf("(\"%s\")", implode('","', $labelIds));
        $table     = Table::withPrefix(self::$definition['table']);
        $rows      = [];

        try {
            $rows = Db::getInstance()
                ->executeS(
                    <<<SQL
SELECT id_order_label FROM $table where id_label in $idsString
SQL
                );
        } catch (Exception $e) {
            ApiLogger::addLog($e, ApiLogger::ERROR);
        }

        return (new Collection(Arr::pluck($rows, 'id_order_label')))
            ->mapInto(__CLASS__)
            ->filter(function ($orderLabel) {
                return Validate::isLoadedObject($orderLabel);
            })
            ->each(function (OrderLabel $orderLabel) {
                self::$cache[$orderLabel->id_label] = $orderLabel;
            })
            ->toArray();
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
            ApiLogger::addLog($e, ApiLogger::ERROR);
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
            OrderLogger::addLog([
                'message' => 'Current order status is ignored.',
                'order'   => $order,
            ]);
            return false;
        }

        if (self::orderHistoryContainsStatus($order, $newOrderStatus)) {
            OrderLogger::addLog([
                'message' => "$orderLabel->id_label] New order status '$newOrderStatus' is already present in order history.",
                'order'   => $order,
            ]);
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
