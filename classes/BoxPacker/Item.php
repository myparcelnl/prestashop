<?php
/**
 * Box packing (3D bin packing, knapsack problem)
 * @author Doug Wright
 * @copyright 2012-2016 Doug Wright
 * @license MIT
 */

namespace MyParcelModule\BoxPacker;

if (!defined('_PS_VERSION_')) {
    return;
}

require_once dirname(__FILE__).'/../../myparcel.php';

/**
 * An item to be packed
 * @author Doug Wright
 * @package MyParcelModule\BoxPacker
 */
interface Item
{
    /**
     * Item SKU etc
     * @return string
     */
    public function getDescription();

    /**
     * Item width in mm
     * @return int
     */
    public function getWidth();

    /**
     * Item length in mm
     * @return int
     */
    public function getLength();

    /**
     * Item depth in mm
     * @return int
     */
    public function getDepth();

    /**
     * Item weight in g
     * @return int
     */
    public function getWeight();

    /**
     * Item volume in mm^3
     * @return int
     */
    public function getVolume();

    /**
     * Does this item need to be kept flat?
     * XXX not yet used, all items are kept flat
     * @return bool
     */
    public function getKeepFlat();
}
