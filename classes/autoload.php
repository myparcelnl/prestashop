<?php
/**
 * Copyright (C) 2017 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

spl_autoload_register(
    function ($class) {
        if (in_array($class, array(
            'MyParcelModule\\BoxPacker\\Box',
            'MyParcelModule\\BoxPacker\\BoxList',
            'MyParcelModule\\BoxPacker\\Item',
            'MyParcelModule\\BoxPacker\\ItemList',
            'MyParcelModule\\BoxPacker\\PackedBox',
            'MyParcelModule\\BoxPacker\\PackedBoxList',
            'MyParcelModule\\BoxPacker\\Packer',
            'MyParcelModule\\BoxPacker\\VolumePacker',
            'MyParcelModule\\BoxPacker\\WeightRedistribution',
        ))) {
            // project-specific namespace prefix
            $prefix = 'MyParcelModule\\BoxPacker\\';

            // base directory for the namespace prefix
            $baseDir = dirname(__FILE__).'/BoxPacker/';

            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }

            // get the relative class name
            $relativeClass = substr($class, $len);

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

            // require it
            require $file;
        }

        if (in_array($class, array(
            'MyParcelBrievenbuspakjeItem',
            'MyParcelCarrierDeliverySetting',
            'MyParcelDeliveryOption',
            'MyParcelMailboxPackage',
            'MyParcelObjectModel',
            'MyParcelOrder',
            'MyParcelOrderHistory',
            'MyParcelTour',
        ))) {
            // base directory for the namespace prefix
            $baseDir = dirname(__FILE__).'/';

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $baseDir.$class.'.php';

            // require it
            require $file;
        }
    }
);
