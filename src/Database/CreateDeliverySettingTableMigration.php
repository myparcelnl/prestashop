<?php

namespace Gett\MyParcel\Database;

class CreateDeliverySettingTableMigration
{
    public static function up(): bool
    {
        $sql = <<<'SQL'
                CREATE TABLE IF NOT EXISTS `{PREFIX}myparcel_delivery_settings` (
  `id_delivery_setting` int(11) NOT NULL AUTO_INCREMENT,
  `id_cart` int(11) NOT NULL,
  `delivery_settings` text,
  PRIMARY KEY (`id_delivery_setting`)
) ENGINE={ENGINE} DEFAULT CHARSET=utf8
SQL;

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->execute(str_replace(['{PREFIX}', '{ENGINE}'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql))
            ;
    }

    public static function down(): bool
    {
        $sql =
            <<<'SQL'
                DROP TABLE IF EXISTS {PREFIX}myparcel_delivery_settings;
SQL;

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->execute(str_replace(['{PREFIX}', '{ENGINE}'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql))
            ;
    }
}