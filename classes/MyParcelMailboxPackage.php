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

use MyParcelModule\BoxPacker\Box;

/**
 * Class MyParcelMailboxPackage
 *
 * @package DVDoug\BoxPacker
 */
class MyParcelMailboxPackage implements Box
{
    public $reference;
    public $outerWidth;
    public $outerLength;
    public $outerDepth;
    public $emptyWeight;
    public $innerWidth;
    public $innerLength;
    public $innerDepth;
    public $maxWeight;
    public $innerVolume;

    /**
     * MpBoxCalcBox constructor.
     *
     * @param string $reference   Reference
     * @param float  $outerWidth  Outer width
     * @param float  $outerLength Outer length
     * @param float  $outerDepth  Outer depth
     * @param float  $emptyWeight Weight when box is empty
     * @param float  $innerWidth  Inner width
     * @param float  $innerLength Inner length
     * @param float  $innerDepth  Inner depth
     * @param float  $maxWeight   Maximum weight
     */
    public function __construct(
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight
    ) {
        $this->reference = $reference;
        $this->outerWidth = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth = $innerDepth;
        $this->maxWeight = $maxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }

    /**
     * Get reference
     *
     * @return string Reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Get outer width
     *
     * @return float Outer width
     */
    public function getOuterWidth()
    {
        return $this->outerWidth;
    }

    /**
     * Get outer length
     *
     * @return float Outer length
     */
    public function getOuterLength()
    {
        return $this->outerLength;
    }

    /**
     * Get outer depth
     *
     * @return float Outer depth
     */
    public function getOuterDepth()
    {
        return $this->outerDepth;
    }

    /**
     * Get empty weight
     *
     * @return float Empty weight
     */
    public function getEmptyWeight()
    {
        return $this->emptyWeight;
    }

    /**
     * Get inner width
     *
     * @return float Inner width
     */
    public function getInnerWidth()
    {
        return $this->innerWidth;
    }

    /**
     * Get inner length
     *
     * @return float Inner length
     */
    public function getInnerLength()
    {
        return $this->innerLength;
    }

    /**
     * Get inner depth
     *
     * @return float Inner depth
     */
    public function getInnerDepth()
    {
        return $this->innerDepth;
    }

    /**
     * Get inner volume
     *
     * @return float Inner volume
     */
    public function getInnerVolume()
    {
        return $this->innerVolume;
    }

    /**
     * Get max weight
     *
     * @return float Max weight
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }
}
