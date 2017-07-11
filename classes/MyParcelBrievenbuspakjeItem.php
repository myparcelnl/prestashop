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

require_once dirname(__FILE__).'/autoload.php';

use MyParcelModule\BoxPacker\Item;

/**
 * Class MyParcelBrievenbuspakjeItem
 */
class MyParcelBrievenbuspakjeItem implements Item
{
    protected $description;
    protected $width;
    protected $length;
    protected $depth;
    protected $weight;
    protected $keepFlat;
    protected $volume;

    /**
     * MpBoxCalcItem constructor.
     *
     * @param string $description Description
     * @param float  $width       Width
     * @param float  $length      Length
     * @param float  $depth       Depth
     * @param float  $weight      Weight
     * @param bool   $keepFlat
     */
    public function __construct($description, $width, $length, $depth, $weight, $keepFlat)
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;

        $this->volume = $this->width * $this->length * $this->depth;
    }

    /**
     * Get description
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get width
     *
     * @return float Width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get length
     *
     * @return float Length
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get depth
     *
     * @return float Depth
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Get weight
     *
     * @return float Weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get volume
     *
     * @return float Volume
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Get keep flat
     *
     * @return bool
     */
    public function getKeepFlat()
    {
        return $this->keepFlat;
    }
}
