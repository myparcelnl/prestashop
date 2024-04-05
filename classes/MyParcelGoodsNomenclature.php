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

use MyParcelModule\MyParcelHttpClient;

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelGoodsNomenclature
 *
 * @since 2.3.0
 */
class MyParcelGoodsNomenclature extends MyParcelObjectModel
{
    const INSTALLED = 0;
    const MISSING = 1;
    const DOWNLOADING = 2;
    const EXTRACTING = 3;
    const INDEXING = 4;
    const GENERATING_TREE = 5;
    const ANALYZING_CHILD_NODES = 6;

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_goods_nomenclature',
        'primary' => 'id_myparcel_goods_nomenclature',
        'fields' => array(
            'code'         => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'CHAR(12)',
            ),
            'parent_code'  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'CHAR(12)',
            ),
            'level_depth'  => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'nleft'        => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'nright'       => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'has_children' => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'db_type'  => 'TINYINT(1)',
            ),
            'language'     => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'CHAR(2)',
            ),
            'description'  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'TEXT',
            ),
        ),
    );

    /** @var string $code */
    public $code;
    /** @var string $parent_code */
    public $parent_code;
    /** @var int $level */
    public $level_depth;
    /** @var int $nleft */
    public $nleft;
    /** @var int $nright */
    public $nright;
    /** @var bool $has_children */
    public $has_children;
    /** @var string $language */
    public $language;
    /** @var string $description */
    public $description;
    // @codingStandardsIgnoreEnd

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
    public static function createDatabase($className = null, $engine = 'MyISAM')
    {
        $success = true;
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.TABLES
                WHERE TABLE_NAME = \''._DB_PREFIX_.pSQL(static::$definition['table']).'\'')
        ) {
            return $success;
        }

        $success &= parent::createDatabase($className, $engine);

        try {
            $success &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.bqSQL(static::$definition['table']).'` ADD FULLTEXT (`description`)');
            $success &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.bqSQL(static::$definition['table']).'` ADD INDEX(`code`, `language`)');
        } catch (Exception $e) {
            $calledClass = get_called_class();
            Logger::addLog("Error while creating {$calledClass} database: {$e->getMessage()}");
            return false;
        }

        return $success;
    }

    /**
     * Install Combined Nomenclature
     *
     * @param int $previousStep
     *
     * @return bool
     *
     * @throws ErrorException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function install($previousStep)
    {
        switch ($previousStep) {
            case static::DOWNLOADING:
                return static::download();
            case static::EXTRACTING:
                return static::extract();
            case static::INDEXING:
                return static::index();
            case static::GENERATING_TREE:
                return static::generateTree();
            case static::ANALYZING_CHILD_NODES:
                return static::analyzeChildNodes();
            default:
                return false;
        }
    }

    /**
     * Download Combined Nomenclature structure file
     *
     * @return bool
     *
     * @since 2.3.0
     *
     * @throws ErrorException
     * @throws PrestaShopException
     */
    protected static function download()
    {
        $curl = new MyParcelHttpClient();
        $curl->download("https://ec.europa.eu/eurostat/ramon/documents/cn_2019/CN_2019_xls.zip", _PS_DOWNLOAD_DIR_.'CN_2019_xls.zip');

        return true;
    }

    /**
     * Extract the CN structure file
     *
     * @return bool
     *
     * @since 2.3.0
     */
    protected static function extract()
    {
        if (!file_exists(_PS_DOWNLOAD_DIR_.'CN_2019_xls.zip')) {
            return false;
        }

        return Tools::ZipExtract(_PS_DOWNLOAD_DIR_.'CN_2019_xls.zip', _PS_DOWNLOAD_DIR_.'cn2019');
    }

    /**
     * Index the CN data
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected static function index()
    {
        $iso = static::getLanguageIso();
        $targetFile = '';
        foreach (scandir(_PS_DOWNLOAD_DIR_.'cn2019') as $file) {
            if (in_array($file, array('.', '..'))) {
                continue;
            }
            list($fileName) = explode('.', $file);
            if (!is_string($fileName)) {
                continue;
            }
            if (in_array($iso, explode(' ', $fileName))) {
                $targetFile = $file;
                break;
            }
        }
        if (!$targetFile) {
            return false;
        }

        if (!$xlsx = MyParcelSimpleXLSX::parse(_PS_DOWNLOAD_DIR_."cn2019/$targetFile")) {
            return false;
        }

        Db::getInstance()->delete(bqSQL(static::$definition['table']), '`language` = \''.pSQL($iso).'\'');
        $rows = $xlsx->rows();
        $parents = array();
        $heading = $rows[0];
        $languagePosition = array_search("${iso}_WITH_DASHES", $heading);
        if ($languagePosition === 'false') {
            return false;
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            $insert = array();
            foreach ($chunk as $row) {
                if (!is_numeric(str_replace(' ', '', $row[0]))) {
                    continue;
                }

                $id = (string) $row[0];
                $level = (int) $row[2];
                if ($level === 1) {
                    $parents = array($level => $id);
                } elseif ($level > 1) {
                    $parents[$level] = $id;
                } else {
                    continue;
                }

                $insert[] = array(
                    'code'         => $id,
                    'parent_code'  => $level > 1 ? $parents[$level - 1] : '',
                    'level_depth'  => $level,
                    'nright'       => '',
                    'nleft'        => '',
                    'has_children' => false,
                    'language'     => pSQL($iso),
                    'description'  => pSQL(ltrim($row[$languagePosition], "{$row[4]} ")),
                );
            }
            try {
                Db::getInstance()->insert(
                    bqSQL(static::$definition['table']),
                    $insert
                );
            } catch (Exception $e) {
            }
        }
        MyParcelTools::recursiveDeleteOnDisk(_PS_DOWNLOAD_DIR_.'cn2019');
        @unlink(_PS_DOWNLOAD_DIR_.'CN_2019_xls.zip');

        return true;
    }

    /**
     * Generate the tree
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected static function generateTree()
    {
        static::regenerateEntireNestedSet();

        return true;
    }

    /**
     * Analyze child nodes
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function analyzeChildNodes()
    {
        $iso = static::getLanguageIso();
        $sql = new DbQuery();
        $sql->select('DISTINCT `parent_code`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`language` = \''.pSQL($iso).'\'');
        $parentCodes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($parentCodes) || empty($parentCodes)) {
            return false;
        }
        $parentCodes = array_filter(array_column($parentCodes, 'parent_code'));
        return Db::getInstance()->execute("
            UPDATE `"._DB_PREFIX_.(static::$definition['table'])."`
            SET `has_children` = IF(`code` IN ('".implode("','", array_map('pSQL', $parentCodes))."'), 1, 0)
            WHERE `language` = '".pSQL($iso)."'
        ");
    }

    /**
     * Search for an entry
     *
     * @param string $query Search query
     *
     * @return bool|array
     *
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function search($query)
    {
        $iso = static::getLanguageIso();
        $sql = new DbQuery();
        $sql->select('`description`, `code`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`language` = \''.pSQL($iso).'\'');
        $sql->limit(20);
        if (count(explode(' ', $query)) <= 1) {
            $sql->where('`description` LIKE \'%'.pSQL($query).'%\'');
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } else {
            $sql->where('MATCH(`description`) AGAINST (\''.pSQL($query).'\')');
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        }

        foreach ($results as &$result) {
            $result = array(
                'name' => $result['description'],
                'code' => $result['code'],
            );
        }

        return $results;
    }

    /**
     * Browse a category
     *
     * @param string|null $parentCode Parent CN Code, leave empty to return first level
     * @param string|null $path       Path to prepend
     *
     * @return array react-treebeard children array
     *
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function browse($parentCode = null, $path = null)
    {
        if (!$parentCode) {
            $sql = new DbQuery();
            $sql->select('`code`, `description`');
            $sql->from(bqSQL(static::$definition['table']));
            $sql->where('`language` = \''.pSQL(static::getLanguageIso()).'\'');
            $sql->where('`level_depth` = 1');
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            foreach ($results as $path => &$result) {
                $result = array(
                    'name'     => $result['description'],
                    'code'     => $result['code'],
                    'path'     => (string) $path,
                    'children' => array(),
                );
            }
        } else {
            $sql = new DbQuery();
            $sql->select('`code`, `description`, `has_children`');
            $sql->from(bqSQL(static::$definition['table']));
            $sql->where('`language` = \''.pSQL(static::getLanguageIso()).'\'');
            $sql->where('`parent_code` = \''.pSQL($parentCode).'\'');
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            foreach ($results as $index => &$result) {
                $newResult = array(
                    'name'     => $result['description'],
                    'code'     => $result['code'],
                    'path'     => "{$path}.children.${index}",
                );
                if ($result['has_children']) {
                    $newResult['children'] = array();
                }
                $result = $newResult;
            }
        }

        return $results;
    }

    /**
     * Navigate to a category
     *
     * @param string $code CN Code
     *
     * @return array react-treebeard children array
     *
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function navigate($code)
    {
        // Retrieve the path to this node first
        $iso = static::getLanguageIso();
        if (Tools::strlen($code) < 12) {
            $sql = new DbQuery();
            $sql->select('`code`');
            $sql->from(bqSQL(static::$definition['table']));
            $sql->where('`language` = \''.pSQL($iso).'\'');
            $sql->where('`code` LIKE \''.pSQL($code).'%\'');
            $sql->orderBy('`code` ASC');
            $code = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        $sql = new DbQuery();
        $sql->select('`code`, `description`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`language` = \''.pSQL(static::getLanguageIso()).'\'');
        $sql->where('`level_depth` = 1');
        $mainTree = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($mainTree)) {
            return array();
        }
        foreach ($mainTree as $index => &$result) {
            $result = array(
                'name'     => $result['description'],
                'code'     => $result['code'],
                'path'     => (string) $index,
                'children' => array(),
            );
        }

        $fullPath = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("
        SELECT parent.`code`, parent.`description`, parent.`has_children`
        FROM `"._DB_PREFIX_.bqSQL(static::$definition['table'])."` AS `node`,
             `"._DB_PREFIX_.bqSQL(static::$definition['table'])."` AS `parent`
        WHERE node.`nleft` BETWEEN parent.`nleft` AND parent.`nright`
            AND node.`code` = '".pSQL($code)."'
            AND node.`language` = '".pSQL($iso)."'
            AND parent.`language` = '".pSQL($iso)."'
        ORDER BY parent.`nleft`");
        if (!is_array($fullPath) || !$code) {
            return $mainTree;
        }

        $finger = &$mainTree;
        $path = '';
        foreach ($fullPath as $depth => $singleNode) {
            $sql = new DbQuery();
            $sql->select('`code`, `description`, `has_children`');
            $sql->from(bqSQL(static::$definition['table']));
            $sql->where('`language` = \''.pSQL(static::getLanguageIso()).'\'');
            $sql->where('`parent_code` = \''.pSQL($singleNode['code']).'\'');
            $children = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            foreach ($children as $index => &$result) {
                $newResult = array(
                    'name'    => $result['description'],
                    'code'    => $result['code'],
                    'toggled' => false,
                    'path'    => $index,
                    'active'  => $result['code'] === $code,
                );
                if ($result['has_children']) {
                    $newResult['children'] = array();
                }
                $result = $newResult;
            }

            foreach ($finger as $index => &$pointerNode) {
                if ($pointerNode['code'] === $singleNode['code']) {
                    if (!$singleNode['has_children']) {
                        break;
                    }
                    $path .= "{$index}.children.";
                    foreach ($children as &$child) {
                        $child['path'] = "{$path}{$child['path']}";
                    }
                    $pointerNode['children'] = $children;
                    $pointerNode['toggled'] = true;
                    $finger = &$pointerNode['children'];
                }
            }
        }

        return $mainTree;
    }

    /**
     * Check if data is installed
     *
     * @return bool
     *
     * @since 2.3.0
     *
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function isInstalled()
    {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.pSQL(static::$definition['table']).'\''
        )) {
            MyParcelGoodsNomenclature::createDatabase();
        }

        $sql = new DbQuery();
        $sql->select('`code`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`language` = \''.pSQL(static::getLanguageIso()).'\'');
        $sql->where('`has_children` = 1');

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Get supported language 2-letter iso code
     *
     * @return string
     *
     * @since 2.3.0
     */
    protected static function getLanguageIso()
    {
        $iso = Tools::strtoupper(Context::getContext()->language->iso_code);

        return in_array($iso, array('EN', 'BG', 'CS', 'DA', 'DE', 'EL', 'ES', 'ET', 'FI', 'FR', 'HR', 'HU', 'IT', 'LT', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SL', 'SV')) ? $iso : 'EN';
    }

    /**
     * Re-calculate the nleft/nright values of all branches of the nested set tree
     *
     * @since 2.3.0
     */
    public static function regenerateEntireNestedSet()
    {
        $sql = new DbQuery();
        $sql->select('cn.`code`, cn.`parent_code`');
        $sql->from(bqSQL(static::$definition['table']), 'cn');
        $sql->where('cn.`language` = \''.pSQL(static::getLanguageIso()).'\'');
        $sql->orderBy('cn.`parent_code`');
        $items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $itemsArray = array();
        foreach ($items as $item) {
            $itemsArray[$item['parent_code'] ?: 0]['children'][] = $item['code'];
        }
        $n = 1;

        if (isset($itemsArray[0]) && $itemsArray[0]['children']) {
            foreach ($itemsArray[0]['children'] as $code ) {
                static::subTree($itemsArray, $code, $n);
            }
        }
    }

    /**
     * @param array  $items
     * @param string $code
     * @param int    $n
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected static function subTree(&$items, $code, &$n)
    {
        $iso = static::getLanguageIso();
        $left = $n++;
        if (isset($items[$code]['children'])) {
            foreach ($items[$code]['children'] as $subcode) {
                static::subTree($items, $subcode, $n);
            }
        }
        $right = (int) $n++;

        Db::getInstance()->update(
            bqSQL(static::$definition['table']),
            array(
                'nleft'  => (int) $left,
                'nright' => (int) $right,
            ),
            '`code` = \''.pSQL($code).'\' AND `language` = \''.pSQL($iso).'\'',
            1
        );
    }
}
