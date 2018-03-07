<?php
/**
 * 2017-2018 DM Productions B.V.
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
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

require_once dirname(__FILE__).'/../myparcel.php';

use MyParcelModule\BoxPacker\Item;

/**
 * Class MyParcelBrievenbuspakjeItem
 */
class MyParcelBrievenbuspakjeItem implements Item
{
    /** @var string $description */
    protected $description;
    /** @var float $width */
    protected $width;
    /** @var float $length */
    protected $length;
    /** @var float $depth */
    protected $depth;
    /** @var float $weight */
    protected $weight;
    /** @var bool $keepFlat */
    protected $keepFlat;
    /** @var float $volume */
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
     *
     * @since 2.0.0
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
     *
     * @since 2.0.0
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get width
     *
     * @return float Width
     *
     * @since 2.0.0
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get length
     *
     * @return float Length
     *
     * @since 2.0.0
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get depth
     *
     * @return float Depth
     *
     * @since 2.0.0
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Get weight
     *
     * @return float Weight
     *
     * @since 2.0.0
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get volume
     *
     * @return float Volume
     *
     * @since 2.0.0
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Get keep flat
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function getKeepFlat()
    {
        return $this->keepFlat;
    }
}
