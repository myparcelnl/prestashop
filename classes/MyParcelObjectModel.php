<?php
/**
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelObjectModel
 */
class MyParcelObjectModel extends ObjectModel
{
    /**
     *  Create the database table with its columns. Similar to the createColumn() method.
     *
     * @param string|null $className Class name
     * @param string      $engine
     *
     * @return bool Indicates whether the database was successfully added
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function createDatabase($className = null, $engine = _MYSQL_ENGINE_)
    {
        $success = true;

        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = static::getDefinition($className);
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'` (';
        $sql .= '`'.$definition['primary'].'` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,';
        foreach ($definition['fields'] as $fieldName => $field) {
            if ($fieldName === $definition['primary'] || (isset($field['lang']) && $field['lang'])) {
                continue;
            }
            $sql .= '`'.$fieldName.'` '.$field['db_type'];
            if (isset($field['required'])) {
                $sql .= ' NOT NULL';
            }
            if (isset($field['default'])) {
                $sql .= ' DEFAULT \''.$field['default'].'\'';
            }
            $sql .= ',';
        }
        $sql = trim($sql, ',');
        $sql .= ') ENGINE='.pSQL($engine).' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

        try {
            $success &= \Db::getInstance()->execute($sql);
        } catch (\PrestaShopException $exception) {
            static::dropDatabase($className);

            return false;
        }

        if (isset($definition['multilang']) && $definition['multilang']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'_lang` (';
            $sql .= '`'.$definition['primary'].'` INT(11) UNSIGNED NOT NULL,';
            foreach ($definition['fields'] as $fieldName => $field) {
                if ($fieldName === $definition['primary'] || !(isset($field['lang']) && $field['lang'])) {
                    continue;
                }
                $sql .= '`'.$fieldName.'` '.$field['db_type'];
                if (isset($field['required'])) {
                    $sql .= ' NOT NULL';
                }
                if (isset($field['default'])) {
                    $sql .= ' DEFAULT \''.$field['default'].'\'';
                }
                $sql .= ',';
            }

            // Lang field
            $sql .= '`id_lang` INT(11) DEFAULT NULL,';
            if (isset($definition['multilang_shop']) && $definition['multilang_shop']) {
                $sql .= '`id_shop` INT(11) DEFAULT NULL,';
            }

            // Primary key
            $sql .= 'PRIMARY KEY (`'.bqSQL($definition['primary']).'`, `id_lang`)';


            $sql .= ') ENGINE='.pSQL($engine).' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

            try {
                $success &= \Db::getInstance()->execute($sql);
            } catch (\PrestaShopException $exception) {
                static::dropDatabase($className);

                return false;
            }
        }

        if (isset($definition['multishop']) && $definition['multishop']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'_shop` (';
            $sql .= '`'.$definition['primary'].'` INT(11) UNSIGNED NOT NULL,';
            foreach ($definition['fields'] as $fieldName => $field) {
                if ($fieldName === $definition['primary'] || !(isset($field['shop']) && $field['shop'])) {
                    continue;
                }
                $sql .= '`'.$fieldName.'` '.$field['db_type'];
                if (isset($field['required'])) {
                    $sql .= ' NOT NULL';
                }
                if (isset($field['default'])) {
                    $sql .= ' DEFAULT \''.$field['default'].'\'';
                }
                $sql .= ',';
            }

            // Shop field
            $sql .= '`id_shop` INT(11) DEFAULT NULL,';

            // Primary key
            $sql .= 'PRIMARY KEY (`'.bqSQL($definition['primary']).'`, `id_shop`)';

            $sql .= ') ENGINE='.pSQL($engine).' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

            try {
                $success &= \Db::getInstance()->execute($sql);
            } catch (\PrestaShopException $exception) {
                static::dropDatabase($className);

                return false;
            }
        }

        return $success;
    }

    /**
     * Drop the database for this ObjectModel
     *
     * @param string|null $className Class name
     *
     * @return bool Indicates whether the database was successfully dropped
     * @throws PrestaShopException
     */
    public static function dropDatabase($className = null)
    {
        $success = true;
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = \ObjectModel::getDefinition($className);

        $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
            .bqSQL($definition['table']).'`');

        if (isset($definition['multilang']) && $definition['multilang']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
                .bqSQL($definition['table']).'_lang`');
        }

        if (isset($definition['multishop']) && $definition['multishop']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
                .bqSQL($definition['table']).'_shop`');
        }

        return $success;
    }

    /**
     * Get columns in database
     *
     * @param string|null $className Class name
     *
     * @return array|false|\mysqli_result|null|\PDOStatement|resource
     */
    public static function getDatabaseColumns($className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = \ObjectModel::getDefinition($className);
        $sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=\''._DB_NAME_.'\' AND TABLE_NAME=\''
            ._DB_PREFIX_.pSQL($definition['table']).'\'';

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Add a column in the table relative to the ObjectModel.
     * This method uses the $definition property of the ObjectModel,
     * with some extra properties.
     *
     * Example:
     * 'table'        => 'tablename',
     * 'primary'      => 'id',
     * 'fields'       => array(
     *     'id'     => array('type' => static::TYPE_INT, 'validate' => 'isInt'),
     *     'number' => array(
     *         'type'     => static::TYPE_STRING,
     *         'db_type'  => 'varchar(20)',
     *         'required' => true,
     *         'default'  => '25'
     *     ),
     * ),
     *
     * The primary column is date_add automatically as INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT. The other columns
     * require an extra parameter, with the type of the column in the database.
     *
     * @param string      $name             Column name
     * @param string      $columnDefinition Column type definition
     * @param string|null $className        Class name
     *
     * @return bool Indicates whether the column was successfully date_add
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function createColumn($name, $columnDefinition, $className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = static::getDefinition($className);
        $sql = 'ALTER TABLE `'._DB_PREFIX_.bqSQL($definition['table']).'`';
        $sql .= ' ADD COLUMN `'.bqSQL($name).'` '.bqSQL($columnDefinition['db_type']).'';
        if ($name === $definition['primary']) {
            $sql .= ' INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT';
        } else {
            if (isset($columnDefinition['required']) && $columnDefinition['required']) {
                $sql .= ' NOT NULL';
            }
            if (isset($columnDefinition['default'])) {
                $sql .= ' DEFAULT "'.pSQL($columnDefinition['default']).'"';
            }
        }

        return (bool) \Db::getInstance()->execute($sql);
    }

    /**
     *  Create in the database every column detailed in the $definition property that are
     *  missing in the database.
     *
     * @param string|null $className Class name
     *
     * @return bool Indicates whether the missing columns were successfully date_add
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     * @todo: Support multishop and multilang
     */
    public static function createMissingColumns($className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $success = true;

        $definition = static::getDefinition($className);
        $columns = static::getDatabaseColumns();
        foreach ($definition['fields'] as $columnName => $columnDefinition) {
            //column exists in database
            $exists = false;
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] === $columnName) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $success &= static::createColumn($columnName, $columnDefinition);
            }
        }

        return $success;
    }
}
