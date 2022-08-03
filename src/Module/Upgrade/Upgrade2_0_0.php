<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class Upgrade2_0_0 extends AbstractUpgrade
{
    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function dropTables(): void
    {
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getOrderDataTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getProductSettingsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getSettingsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getDeliveryOptionsTable()}`");
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function upgrade(): void
    {
        $this->dropTables();
        $this->addTables();

        $this->migrateDeliveryOptions();
        $this->migrateSettings();
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addTables(): void
    {
        $this->db->execute(
            <<<EOF
CREATE TABLE `{$this->getOrderDataTable()}`
(
    `id`                 int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `externalIdentifier` varchar(10),
    `deliveryOptions`    text,
    `shipments`          text
);
EOF

        );

        $this->db->execute(
            <<<EOF
CREATE TABLE `{$this->getProductSettingsTable()}`
(
    `id`         int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` int(10) NOT NULL
);
EOF
        );

        $this->db->execute(
            <<<EOF
CREATE TABLE `{$this->getSettingsTable()}`
(
    `id`    int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`  varchar(255) NOT NULL,
    `value` text         NOT NULL
);
EOF
        );
        $this->db->execute(
            <<<EOF
CREATE TABLE `{$this->getDeliveryOptionsTable()}`
(
    `id`              int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cartId`          varchar(10),
    `deliveryOptions` text
);
EOF
        );

        DefaultLogger::debug(
            'Created tables',
            [
                'tables' => [$this->getOrderDataTable(), $this->getProductSettingsTable(), $this->getSettingsTable()],
            ]
        );
    }

    /**
     * @return string
     */
    private function getDeliveryOptionsTable(): string
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
    private function getProductSettingsTable(): string
    {
        return Table::withPrefix(Table::TABLE_PRODUCT_SETTINGS);
    }

    /**
     * @return string
     */
    private function getSettingsTable(): string
    {
        return Table::withPrefix(Table::TABLE_SETTINGS);
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \PrestaShopException
     */
    private function migrateDeliveryOptions(): void
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from('myparcelbe_delivery_settings');

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
        $deliveryOptionsTable = $this->getDeliveryOptionsTable();

        $strr= array_reduce($newValues, static function ($acc, $val) {
            $acc .= sprintf("('%s'),\n", implode("','", $val));

            return $acc;
        }, '');

        $this->db->execute(
            "INSERT INTO `$deliveryOptionsTable` (`cartId`, `deliveryOptions`, `deliveryMethod`) VALUES $newValuesString"
        );

        DefaultLogger::debug('Migrated delivery options', compact('oldValues', 'newValues'));
    }

    private function migrateSettings()
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from('configuration');
        $query->where('name LIKE "myparcelbe_%"');

        $oldValues = $this->db->executeS($query);
        $newValues = [];

        $newValuesString      = implode(',', $newValues);
        $deliveryOptionsTable = $this->getDeliveryOptionsTable();

        DefaultLogger::debug('Migrated delivery options', compact('oldValues', 'newValues'));

        $config = [
            'MYPARCELBE_API_KEY'                             => 'general.apiKey',
            'MYPARCELBE_API_LOGGING'                         => 'general.apiLogging',
            'MYPARCELBE_BPOST'                               => '',
            'MYPARCELBE_AGE_CHECK'                           => '',
            'MYPARCELBE_CONCEPT_FIRST'                       => '',
            'MYPARCELBE_CUSTOMS_CODE'                        => '',
            'MYPARCELBE_CUSTOMS_FORM'                        => '',
            'MYPARCELBE_CUSTOMS_ORIGIN'                      => '',
            'MYPARCELBE_DEFAULT_CUSTOMS_CODE'                => '',
            'MYPARCELBE_DEFAULT_CUSTOMS_ORIGIN'              => '',
            'MYPARCELBE_DELIVERED_ORDER_STATUS'              => '',
            'MYPARCELBE_DELIVERY_OPTIONS_PRICE_FORMAT'       => '',
            'MYPARCELBE_DPD'                                 => '',
            'MYPARCELBE_IGNORE_ORDER_STATUS'                 => '',
            'MYPARCELBE_INSURANCE'                           => '',
            'MYPARCELBE_INSURANCE_BELGIUM'                   => '',
            'MYPARCELBE_INSURANCE_FROM_PRICE'                => '',
            'MYPARCELBE_INSURANCE_MAX_AMOUNT'                => '',
            'MYPARCELBE_LABEL_CREATED_ORDER_STATUS'          => '',
            'MYPARCELBE_LABEL_DESCRIPTION'                   => '',
            'MYPARCELBE_LABEL_OPEN_DOWNLOAD'                 => '',
            'MYPARCELBE_LABEL_POSITION'                      => '',
            'MYPARCELBE_LABEL_PROMPT_POSITION'               => '',
            'MYPARCELBE_LABEL_SCANNED_ORDER_STATUS'          => '',
            'MYPARCELBE_LABEL_SIZE'                          => '',
            'MYPARCELBE_ORDER_NOTIFICATION_AFTER'            => '',
            'MYPARCELBE_PACKAGE_FORMAT'                      => '',
            'MYPARCELBE_PACKAGE_TYPE'                        => '',
            'MYPARCELBE_POSTNL'                              => '',
            'MYPARCELBE_RECIPIENT_ONLY'                      => '',
            'MYPARCELBE_RETURN_PACKAGE'                      => '',
            'MYPARCELBE_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS' => '',
            'MYPARCELBE_SHARE_CUSTOMER_EMAIL'                => '',
            'MYPARCELBE_SHARE_CUSTOMER_PHONE'                => '',
            'MYPARCELBE_SIGNATURE_REQUIRED'                  => '',
            'MYPARCELBE_STATUS_CHANGE_MAIL'                  => '',
            'MYPARCELBE_USE_ADDRESS2_AS_STREET_NUMBER'       => '',
            'MYPARCELBE_WEBHOOK_HASH'                        => '',
            'MYPARCELBE_WEBHOOK_ID'                          => '',
        ];

        $this->db->execute(
            "INSERT INTO `{$this->getSettingsTable()}` (`storeId`, `name`, `value`) VALUES $newValuesString"
        );
    }
}
