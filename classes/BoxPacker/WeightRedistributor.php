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
class WeightRedistributor
{
    /**
     * List of box sizes available to pack items into
     * @var BoxList
     */
    protected $boxes;

    /**
     * Constructor
     */
    public function __construct(BoxList $boxList)
    {
        $this->boxes = clone $boxList;
    }

    /**
     * Given a solution set of packed boxes, repack them to achieve optimum weight distribution
     *
     * @param PackedBoxList $originalBoxes
     * @return PackedBoxList
     */
    public function redistributeWeight(PackedBoxList $originalBoxes)
    {

        $targetWeight = $originalBoxes->getMeanWeight();

        $packedBoxes = new PackedBoxList();

        $overWeightBoxes = array();
        $underWeightBoxes = array();
        foreach (clone $originalBoxes as $packedBox) {
            $boxWeight = $packedBox->getWeight();
            if ($boxWeight > $targetWeight) {
                $overWeightBoxes[] = $packedBox;
            } elseif ($boxWeight < $targetWeight) {
                $underWeightBoxes[] = $packedBox;
            } else {
                $packedBoxes->insert($packedBox); //target weight, so we'll keep these
            }
        }

        do { //Keep moving items from most overweight box to most underweight box
            $tryRepack = false;

            foreach ($underWeightBoxes as $u => $underWeightBox) {
                foreach ($overWeightBoxes as $o => $overWeightBox) {
                    $overWeightBoxItems = $overWeightBox->getItems()->asArray();

                    //For each item in the heavier box, try and move it to the lighter one
                    foreach ($overWeightBoxItems as $oi => $overWeightBoxItem) {
                        if ($underWeightBox->getWeight() + $overWeightBoxItem->getWeight() > $targetWeight) {
                            continue; //skip if moving this item would hinder rather than help weight distribution
                        }

                        $newItemsForLighterBox = clone $underWeightBox->getItems();
                        $newItemsForLighterBox->insert($overWeightBoxItem);

                        $newLighterBoxPacker = new Packer(); //we may need a bigger box
                        $newLighterBoxPacker->setBoxes($this->boxes);
                        $newLighterBoxPacker->setItems($newItemsForLighterBox);
                        $newLighterBox = $newLighterBoxPacker->doVolumePacking()->extract();

                        if ($newLighterBox->getItems()->count() === $newItemsForLighterBox->count()) { //new item fits
                            unset($overWeightBoxItems[$oi]); //now packed in different box

                            $newHeavierBoxPacker = new Packer(); //we may be able to use a smaller box
                            $newHeavierBoxPacker->setBoxes($this->boxes);
                            $newHeavierBoxPacker->setItems($overWeightBoxItems);

                            $newHeavierBoxes = $newHeavierBoxPacker->doVolumePacking();
                            //found an edge case in packing algorithm that *increased* box count
                            if (count($newHeavierBoxes) > 1) {
                                return $originalBoxes;
                            }

                            $overWeightBoxes[$o] = $newHeavierBoxes->extract();
                            $underWeightBoxes[$u] = $newLighterBox;

                            $tryRepack = true; //we did some work, so see if we can do even better
                            usort($overWeightBoxes, array($packedBoxes, 'reverseCompare'));
                            usort($underWeightBoxes, array($packedBoxes, 'reverseCompare'));
                            break 3;
                        }
                    }
                }
            }
        } while ($tryRepack);

        //Combine back into a single list
        $packedBoxes->insertFromArray($overWeightBoxes);
        $packedBoxes->insertFromArray($underWeightBoxes);

        return $packedBoxes;
    }
}
