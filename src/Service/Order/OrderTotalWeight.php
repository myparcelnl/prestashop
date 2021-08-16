<?php

namespace Gett\MyparcelBE\Service\Order;

use Configuration;
use Gett\MyparcelBE\Module\Tools\Tools;

class OrderTotalWeight
{
    /**
     * Returns the weight of the order in grams.
     *
     * @param float|int $orderWeight
     *
     * @return int
     */
    public function convertWeightToGrams($orderWeight): int
    {
        $weight = $orderWeight;

        if ($orderWeight > 0) {
            $weightUnit = strtolower(Configuration::get('PS_WEIGHT_UNIT'));
            switch ($weightUnit) {
                case 't':
                    $weight = Tools::ps_round($orderWeight * 1000000);
                    break;
                case 'kg':
                    $weight = Tools::ps_round($orderWeight * 1000);
                    break;
                case 'lbs':
                    $weight = Tools::ps_round($orderWeight * 453.59237);
                    break;
                case 'oz':
                    $weight = Tools::ps_round($orderWeight * 28.3495231);
                    break;
            }
        }

        return (int) ceil($weight);
    }
}
