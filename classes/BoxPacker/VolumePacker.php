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
 * Actual packer
 * @author Doug Wright
 * @package MyParcelModule\BoxPacker
 */
class VolumePacker
{
    /**
     * Box to pack items into
     * @var Box
     */
    protected $box;

    /**
     * List of items to be packed
     * @var ItemList
     */
    protected $items;

    /**
     * Constructor
     */
    public function __construct(Box $box, ItemList $items)
    {
        $this->box = $box;
        $this->items = $items;
    }

    /**
     * Pack as many items as possible into specific given box
     * @return PackedBox packed box
     */
    public function pack()
    {
        $packedItems = new ItemList();
        $remainingDepth = $this->box->getInnerDepth();
        $remainingWeight = $this->box->getMaxWeight() - $this->box->getEmptyWeight();
        $remainingWidth = $this->box->getInnerWidth();
        $remainingLength = $this->box->getInnerLength();

        $layerWidth = $layerLength = $layerDepth = 0;
        while (!$this->items->isEmpty()) {
            $itemToPack = $this->items->top();

            //skip items that are simply too large
            if ($this->isItemTooLargeForBox($itemToPack, $remainingDepth, $remainingWeight)) {
                $this->items->extract();
                continue;
            }

            $itemWidth = $itemToPack->getWidth();
            $itemLength = $itemToPack->getLength();

            if ($this->fitsGap($itemToPack, $remainingWidth, $remainingLength)) {
                $packedItems->insert($this->items->extract());
                $remainingWeight -= $itemToPack->getWeight();

                $nextItem = !$this->items->isEmpty() ? $this->items->top() : null;
                if ($this->fitsBetterUnrotated($itemToPack, $nextItem, $remainingWidth, $remainingLength)) {
                    $remainingLength -= $itemLength;
                    $layerLength += $itemLength;
                    $layerWidth = max($itemWidth, $layerWidth);
                } else {
                    $remainingLength -= $itemWidth;
                    $layerLength += $itemWidth;
                    $layerWidth = max($itemLength, $layerWidth);
                }
                $layerDepth = max($layerDepth, $itemToPack->getDepth()); //greater than 0

                //allow items to be stacked in place within the same footprint up to current layerdepth
                $maxStackDepth = $layerDepth - $itemToPack->getDepth();
                while (!$this->items->isEmpty() && $this->canStackItemInLayer(
                    $itemToPack,
                    $this->items->top(),
                    $maxStackDepth,
                    $remainingWeight
                )) {
                    $remainingWeight -= $this->items->top()->getWeight();
                    $maxStackDepth -= $this->items->top()->getDepth();
                    $packedItems->insert($this->items->extract());
                }
            } else {
                if ($remainingWidth >= min($itemWidth, $itemLength) && $this->isLayerStarted(
                    $layerWidth,
                    $layerLength,
                    $layerDepth
                )) {
                    $remainingLength += $layerLength;
                    $remainingWidth -= $layerWidth;
                    $layerWidth = $layerLength = 0;
                    continue;
                } elseif ($remainingLength < min($itemWidth, $itemLength) || $layerDepth == 0) {
                    $this->items->extract();
                    continue;
                }

                $remainingWidth = $layerWidth
                    ? min(floor($layerWidth * 1.1), $this->box->getInnerWidth())
                    : $this->box->getInnerWidth();
                $remainingLength = $layerLength
                    ? min(floor($layerLength * 1.1), $this->box->getInnerLength())
                    : $this->box->getInnerLength();
                $remainingDepth -= $layerDepth;

                $layerWidth = $layerLength = $layerDepth = 0;
            }
        }

        return new PackedBox(
            $this->box,
            $packedItems,
            $remainingWidth,
            $remainingLength,
            $remainingDepth,
            $remainingWeight
        );
    }

    /**
     * @param Item $item
     * @param int $remainingDepth
     * @param int $remainingWeight
     * @return bool
     */
    protected function isItemTooLargeForBox(Item $item, $remainingDepth, $remainingWeight)
    {
        return $item->getDepth() > $remainingDepth || $item->getWeight() > $remainingWeight;
    }

    /**
     * Figure out space left for next item if we pack this one in it's regular orientation
     * @param Item $item
     * @param int $remainingWidth
     * @param int $remainingLength
     * @return int
     */
    protected function fitsSameGap(Item $item, $remainingWidth, $remainingLength)
    {
        return min($remainingWidth - $item->getWidth(), $remainingLength - $item->getLength());
    }

    /**
     * Figure out space left for next item if we pack this one rotated by 90deg
     * @param Item $item
     * @param int $remainingWidth
     * @param int $remainingLength
     * @return int
     */
    protected function fitsRotatedGap(Item $item, $remainingWidth, $remainingLength)
    {
        return min($remainingWidth - $item->getLength(), $remainingLength - $item->getWidth());
    }

    /**
     * @param Item $item
     * @param Item|null $nextItem
     * @param $remainingWidth
     * @param $remainingLength
     * @return bool
     */
    protected function fitsBetterUnrotated(Item $item, Item $nextItem = null, $remainingWidth, $remainingLength)
    {
        $fitsSameGap = $this->fitsSameGap($item, $remainingWidth, $remainingLength);
        $fitsRotatedGap = $this->fitsRotatedGap($item, $remainingWidth, $remainingLength);

        return !!($fitsRotatedGap < 0 ||
        ($fitsSameGap >= 0 && $fitsSameGap <= $fitsRotatedGap) ||
        ($item->getWidth() <= $remainingWidth && $nextItem == $item && $remainingLength >= 2 * $item->getLength()));
    }

    /**
     * Does item fit in specified gap
     * @param Item $item
     * @param $remainingWidth
     * @param $remainingLength
     * @return bool
     */
    protected function fitsGap(Item $item, $remainingWidth, $remainingLength)
    {
        return $this->fitsSameGap($item, $remainingWidth, $remainingLength) >= 0 ||
               $this->fitsRotatedGap($item, $remainingWidth, $remainingLength) >= 0;
    }

    /**
     * Figure out if we can stack the next item vertically on top of this rather than side by side
     * Used when we've packed a tall item, and have just put a shorter one next to it
     * @param Item $item
     * @param Item $nextItem
     * @param $maxStackDepth
     * @param $remainingWeight
     * @return bool
     */
    protected function canStackItemInLayer(Item $item, Item $nextItem, $maxStackDepth, $remainingWeight)
    {
        return $nextItem->getDepth() <= $maxStackDepth &&
               $nextItem->getWeight() <= $remainingWeight &&
               $nextItem->getWidth() <= $item->getWidth() &&
               $nextItem->getLength() <= $item->getLength();
    }

    /**
     * @param $layerWidth
     * @param $layerLength
     * @param $layerDepth
     * @return bool
     */
    protected function isLayerStarted($layerWidth, $layerLength, $layerDepth)
    {
        return $layerWidth > 0 && $layerLength > 0 && $layerDepth > 0;
    }
}
