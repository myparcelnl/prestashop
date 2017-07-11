<?php
/**
 * 2017 DM Productions B.V.
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
 * @author     DM Productions B.V. <info@dmp.nl>
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2017 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('_TB_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/autoload.php';

/**
 * Class MyParcelTour
 */
class MyParcelTour extends MyParcelObjectModel
{
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'postnlconf_tour',
        'primary' => 'id_postnlconf_tour',
        'fields'  => array(
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'default' => '0', 'db_type' => 'INT(11) UNSIGNED'),
            'name'        => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'default' => '', 'db_type' => 'VARCHAR(12)'),
            'value'       => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'db_type' => 'TEXT'),
        ),
    );
    /** @var int $id_employee */
    public $id_employee;
    /** @var string $name */
    public $name;
    /** @var string $value */
    public $value;
    // @codingStandardsIgnoreEnd

    /**
     * Enable tour for one or all employees
     *
     * @param string   $tourName   Name of tour
     * @param int|null $idEmployee Employee ID
     *
     * @return bool Whether the tour has been successfully enabled
     */
    public static function enableTour($tourName, $idEmployee = null)
    {
        $success = true;

        if ($idEmployee === null) {
            $employees = Employee::getEmployees();
        } elseif ($idEmployee === 0) {
            return false;
        } else {
            $employees = array(array('id_employee' => (int) $idEmployee));
        }

        foreach ($employees as $employee) {
            if (self::getTourStep($tourName, $employee['id_employee']) !== false) {
                $success &= (bool) Db::getInstance()->update(
                    bqSQL(self::$definition['table']),
                    array(
                        'value' => '',
                    ),
                    '`name` = \''.pSQL($tourName).'\' AND `id_employee` = '.(int) $employee['id_employee']
                );
            } else {
                $success &= Db::getInstance()->insert(
                    bqSQL(self::$definition['table']),
                    array(
                        'id_employee' => (int) $employee['id_employee'],
                        'name'        => pSQL($tourName),
                        'value'       => '',
                    )
                );
            }
        }

        return $success;
    }

    /**
     * Get tour step of tour for employee
     *
     * @param string $tourname   Tour name
     * @param int    $idEmployee Employee ID
     *
     * @return string
     */
    public static function getTourStep($tourname, $idEmployee)
    {
        if (empty($idEmployee)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('value');
        $sql->from(bqSQL(self::$definition['table']));
        $sql->where('`name` = \''.pSQL($tourname).'\'');
        $sql->where('`id_employee` = '.(int) $idEmployee);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Disable tour for one or all employees
     *
     * @param string   $tourName   Name of tour
     * @param int|null $idEmployee Employee ID
     *
     * @return bool Whether the tour has been successfully disabled
     */
    public static function disableTour($tourName, $idEmployee = null)
    {
        $success = true;

        if ($idEmployee === null) {
            $employees = Employee::getEmployees();
        } elseif ($idEmployee === 0) {
            return false;
        } else {
            $employees = array(array('id_employee' => $idEmployee));
        }

        foreach ($employees as $employee) {
            if (self::getTourStep($tourName, $employee['id_employee']) !== false) {
                $success &= (bool) Db::getInstance()->update(
                    bqSQL(self::$definition['table']),
                    array(
                        'value' => 'disabled',
                    ),
                    '`name` = \''.pSQL($tourName).'\' AND `id_employee` = '.(int) $employee['id_employee']
                );
            } else {
                $success &= (bool) Db::getInstance()->insert(
                    bqSQL(self::$definition['table']),
                    array(
                        'id_employee' => (int) $employee['id_employee'],
                        'value'       => pSQL($tourName),
                        'name'        => 'disabled',
                    )
                );
            }
        }

        return $success;
    }

    /**
     * Save tour step
     *
     * @param string $tourName   Tour name
     * @param string $tourStep   Tour step
     * @param int    $idEmployee Employee ID
     *
     * @return bool Whether the tour step has been succesfully saved
     */
    public static function saveTour($tourName, $tourStep, $idEmployee)
    {
        if (empty($idEmployee)) {
            return false;
        }
        Db::getInstance()->update(
            bqSQL(self::$definition['table']),
            array(
                'value' => pSQL($tourStep),
            ),
            '`name` = \''.pSQL($tourName).'\' AND `id_employee` = '.(int) $idEmployee
        );

        Db::getInstance()->insert(
            bqSQL(self::$definition['table']),
            array(
                'name'        => pSQL($tourName),
                'value'       => pSQL($tourStep),
                'id_employee' => (int) $idEmployee,
            ),
            false,
            true,
            Db::INSERT_IGNORE
        );

        return true;
    }
}
