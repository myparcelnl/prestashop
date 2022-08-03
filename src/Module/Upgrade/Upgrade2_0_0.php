<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Upgrade;

use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Database\Table;

class Upgrade2_0_0 extends AbstractUpgrade
{
    protected const LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';

    protected const LEGACY_TABLE_DELIVERY_SETTINGS = 'myparcelnl_delivery_settings';

    protected const LEGACY_TABLE_ORDER_LABEL = 'myparcelnl_order_label';

    protected const LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';

    /**
     * @return void
     */
    public function upgrade(): void
    {
        // $this->dropOldTables();

        // $this->migrateCartDeliveryOptions();
        // $this->migrateConfiguration();
        // $this->migrateOrderData();
        // $this->migrateOrderShipments();
    }

    /**
     * @return void
     */
    protected function dropOldTables(): void
    {
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getOrderDataTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getProductSettingsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getOrderShipmentsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getCartDeliveryOptionsTable()}`");
    }

    /**
     * @return string
     */
    private function getCartDeliveryOptionsTable(): string
    {
        return Table::withPrefix(Table::TABLE_CART_DELIVERY_OPTIONS);
    }

    /**
     * @return string
     */
    private function getOrderDataTable(): string
    {
        return Table::withPrefix(Table::TABLE_ORDER_DATA);
    }

    /**
     * @return string
     */
    private function getOrderShipmentsTable(): string
    {
        return Table::withPrefix(Table::TABLE_ORDER_SHIPMENT);
    }

    /**
     * @return string
     */
    private function getProductSettingsTable(): string
    {
        return Table::withPrefix(Table::TABLE_PRODUCT_SETTINGS);
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \PrestaShopException
     */
    private function migrateCartDeliveryOptions(): void
    {
        $query = new \DbQuery();

        $query->select('*');
        $query->from(self::LEGACY_TABLE_DELIVERY_SETTINGS);

        $oldValues = $this->db->executeS($query);
        $newValues = [];

        foreach ($oldValues as $deliveryOptions) {
            $data     = json_decode($deliveryOptions['delivery_settings'], true);
            $instance = (new DeliveryOptions())->fill($data);

            $newValues[] = [
                'cartId'          => $deliveryOptions['id_cart'],
                'shippingMethod'  => $deliveryOptions['id_delivery_setting'],
                'deliveryOptions' => json_encode($instance->toArray()),
            ];
        }

        $newValuesString      = implode(',', $newValues);
        $deliveryOptionsTable = Table::withPrefix(Table::TABLE_CART_DELIVERY_OPTIONS);

        $strr = array_reduce($newValues, static function ($acc, $val) {
            $acc .= sprintf("('%s'),\n", implode("','", $val));

            return $acc;
        }, '');

        $this->db->execute(
            "INSERT INTO `$deliveryOptionsTable` (`cartId`, `deliveryOptions`, `deliveryMethod`) VALUES $newValuesString"
        );

        DefaultLogger::debug('Migrated delivery options', compact('oldValues', 'newValues'));
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    private function migrateConfiguration(): void
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from('configuration');
        $query->where('name LIKE "myparcelnl_%"');

        $oldValues = $this->db->executeS($query);
        $newValues = [];

        $config = [
            'MYPARCELNL_API_KEY'                             => 'general.apiKey',
            'MYPARCELNL_API_LOGGING'                         => 'general.apiLogging',
            'MYPARCELNL_BPOST'                               => '',
            'MYPARCELNL_AGE_CHECK'                           => '',
            'MYPARCELNL_CONCEPT_FIRST'                       => '',
            'MYPARCELNL_CUSTOMS_CODE'                        => '',
            'MYPARCELNL_CUSTOMS_FORM'                        => '',
            'MYPARCELNL_CUSTOMS_ORIGIN'                      => '',
            'MYPARCELNL_DEFAULT_CUSTOMS_CODE'                => '',
            'MYPARCELNL_DEFAULT_CUSTOMS_ORIGIN'              => '',
            'MYPARCELNL_DELIVERED_ORDER_STATUS'              => '',
            'MYPARCELNL_DELIVERY_OPTIONS_PRICE_FORMAT'       => '',
            'MYPARCELNL_DPD'                                 => '',
            'MYPARCELNL_IGNORE_ORDER_STATUS'                 => '',
            'MYPARCELNL_INSURANCE'                           => '',
            'MYPARCELNL_INSURANCE_BELGIUM'                   => '',
            'MYPARCELNL_INSURANCE_FROM_PRICE'                => '',
            'MYPARCELNL_INSURANCE_MAX_AMOUNT'                => '',
            'MYPARCELNL_LABEL_CREATED_ORDER_STATUS'          => '',
            'MYPARCELNL_LABEL_DESCRIPTION'                   => '',
            'MYPARCELNL_LABEL_OPEN_DOWNLOAD'                 => '',
            'MYPARCELNL_LABEL_POSITION'                      => '',
            'MYPARCELNL_LABEL_PROMPT_POSITION'               => '',
            'MYPARCELNL_LABEL_SCANNED_ORDER_STATUS'          => '',
            'MYPARCELNL_LABEL_SIZE'                          => '',
            'MYPARCELNL_ORDER_NOTIFICATION_AFTER'            => '',
            'MYPARCELNL_PACKAGE_FORMAT'                      => '',
            'MYPARCELNL_PACKAGE_TYPE'                        => '',
            'MYPARCELNL_POSTNL'                              => '',
            'MYPARCELNL_RECIPIENT_ONLY'                      => '',
            'MYPARCELNL_RETURN_PACKAGE'                      => '',
            'MYPARCELNL_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS' => '',
            'MYPARCELNL_SHARE_CUSTOMER_EMAIL'                => '',
            'MYPARCELNL_SHARE_CUSTOMER_PHONE'                => '',
            'MYPARCELNL_SIGNATURE_REQUIRED'                  => '',
            'MYPARCELNL_STATUS_CHANGE_MAIL'                  => '',
            'MYPARCELNL_USE_ADDRESS2_AS_STREET_NUMBER'       => '',
            'MYPARCELNL_WEBHOOK_HASH'                        => '',
            'MYPARCELNL_WEBHOOK_ID'                          => '',
        ];

        foreach ($oldValues as $oldValue) {
            $newValues[] = [
                'name'  => $config[$oldValue['name']],
                'value' => $oldValue['value'],
            ];
        }

        $newValuesString = implode(',', $newValues);

        $this->db->execute("INSERT INTO `configuration` (`storeId`, `name`, `value`) VALUES $newValuesString");
        DefaultLogger::debug('Migrated configuration', compact('oldValues', 'newValues'));
    }

    private function migrateOrderData(): void
    {
        // from
    }

    private function migrateOrderShipments()
    {
        // from order_label to order_shipment
    }
}
