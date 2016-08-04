<?php

class AddressController extends AddressControllerCore
{
	/*
	* module: myparcel
	* date: 2015-11-23 05:33:41
	* version: 1.1.4
	*/
	public function setMedia()
 	{
		parent::setMedia();
	  
		$frontend_plugin = Configuration::get('MYPARCEL_FRONTEND_PLUGIN');

	    if (empty($frontend_plugin))
	        return;

		$myparcel = Module::getInstanceByName('myparcel');

		$url = $myparcel->getMyParcelUrl();
		if(!$myparcel->isPrestashop15()){
			Media::addJsDef(array(
				'MYPARCEL_PAKJEGEMAK_URL' => $url,
			));
		}

		$this->addJS(array(
			__PS_BASE_URI__.'modules/myparcel/js/frontend.js'
		));
 	}
}
