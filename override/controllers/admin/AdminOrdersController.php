<?php
/**
 * Backend orders controller
 * 
 * @copyright Copyright (c) 2014 MyParcel (https://www.myparcel.nl/)
 */

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

        $myParcelFlag = Configuration::get('MYPARCEL_ACTIVE');

        $this->context->smarty->assign(
            array(
                'myParcel'          => $myParcelFlag,
                'prestaShopVersion' => substr(_PS_VERSION_, 0, 3),
            )
        );

        if (true == $myParcelFlag) {
            if ('' == session_id()) {
                session_start();
            }

            $_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'] = '';
        }
    }
}