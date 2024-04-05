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

/**
 * List of items to be packed, ordered by volume
 * @author Doug Wright
 * @package MyParcelModule\BoxPacker
 */
class ItemList extends \SplMaxHeap
{

    /**
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @see \SplMaxHeap::compare()
     */
    public function compare($itemA, $itemB)
    {
        if ($itemA->getVolume() > $itemB->getVolume()) {
            return 1;
        } elseif ($itemA->getVolume() < $itemB->getVolume()) {
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * Get copy of this list as a standard PHP array
     * @return array
     */
    public function asArray()
    {
        $return = array();
        foreach (clone $this as $item) {
            $return[] = $item;
        }
        return $return;
    }
}
