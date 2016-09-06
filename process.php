<?php
/**
 * Process module actions
 *
 * @copyright Copyright (c) 2013 MyParcel (https://www.myparcel.nl/)
 */

if ('' == session_id()) {
    session_start();
}

require(dirname(__FILE__) . '/../../config/config.inc.php');

define('MYPARCEL_URL', 'https://www.myparcel.nl/');

/**
 * Retrieves address from the string
 *
 * @param string $address
 * @return array
 */
function getAddressComponents($address)
{
    $ret = array();
    $ret['house_number'] = '';
    $ret['number_addition'] = '';
    //$address = 'Markerkant 10 11E';
    $address = str_replace(array('?', '*', '[', ']', ',', '!'), ' ', $address);
    $address = preg_replace('/\s\s+/', ' ', $address);

    $matches = _splitStreet($address);

    if (!empty($matches[2])) {
        $ret['street'] = trim($matches[1]);
        $ret['house_number'] = trim($matches[3]);
        $ret['number_addition'] = trim($matches[4]);
    } else {
        $ret['street'] = $address;
    }

	/** START @Since the fix for negative house number (64-69) **/
    if (strlen($ret['street']) && substr($ret['street'], -1) == '-') {
        $ret['street'] = str_replace(' -', '', $ret['street']);
        return getAddressComponents( $ret['street']);
    }
    /** END @Since the fix for negative house number (64-69) **/
	
    return $ret;
}

function _splitStreet($fullStreet)
{
    $split_street_regex = '~(?P<street>.*?)\s?(?P<street_suffix>(?P<number>[\d]+)-?(?P<number_suffix>[a-zA-Z/\s]{0,5}$|[0-9/]{0,5}$|\s[a-zA-Z]{1}[0-9]{0,3}$))$~';
    $fullStreet = preg_replace("/[\n\r]/", "", $fullStreet);
    $result = preg_match($split_street_regex, $fullStreet, $matches);

    if (!$result || !is_array($matches) || $fullStreet != $matches[0]) {
        if ($fullStreet != $matches[0]) {
            // Characters are gone by preg_match
            exit('Something went wrong with splitting up address ' . $fullStreet);
        } else {
            // Invalid full street supplied
            exit('Invalid full street supplied: ' . $fullStreet);
        }
    }

    return $matches;
}

/**
 * JAVASCRIPT ACTIONS
 */
if (isset($_GET['action'])) {
    /**
     * MYPARCEL STATUS UPDATE
     *
     * Every time this script is called, it will check if an update of the order statuses is required
     * Depending on the last update with a timeout, since TNT updates our status 2 times a day anyway
     *
     * NOTE: Increasing this timeout is POINTLESS, since TNT updates our statuses only 2 times a day
     *       Please save our bandwidth and use the Track&Trace link to get the actual status. Thanks
     */

	$myparcel = Module::getInstanceByName('myparcel');
	 
    if (isset($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'])
        && !empty($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'])
    ) {
        $visibleConsignments = str_replace('|', ', ', trim($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'], '|'));

        $sql = "SELECT *
                FROM `" . _DB_PREFIX_ . "myparcel`
                WHERE `consignment_id` IN (" . $visibleConsignments . ")
                AND `tnt_final` = 0
                AND `tnt_updated_on` < '" . date('Y-m-d H:i:s', time() - 43200) . "'";

        $result = Db::getInstance()->ExecuteS($sql, true, false);

        $consignments = array();

        foreach ($result as $row) {
            $consignments[] = $row['consignment_id'];
        }

        /** START Fix consignment update TNT error when one of the ids is invalid **/
        if (count($consignments) > 0) {
            foreach ($consignments as $consignment) {
                $statusFile = file(MYPARCEL_URL . 'status/tnt/' . $consignment);
                if ($statusFile) {
                    $row = $statusFile[0];
                    $row = explode('|', $row);

                    if (count($row) != 3) {
                        continue;
                    }

                    Db::getInstance()->update(
                        'myparcel',
                        array(
                            'tnt_status' => trim($row[2]),
                            'tnt_updated_on' => date('Y-m-d H:i:s'),
                            'tnt_final' => (int)$row[1],
                        ),
                        '`consignment_id` = "' . $row[0] . '"'
                    );
                }
            }
        }
        /** END Fix consignment update TNT error when one of the ids is invalid **/
    }

    /**
     * PLUGIN POPUP CREATE / RETOUR
     */
    if ($_GET['action'] == 'post' && is_numeric($_GET['order_id'])) {
        // Determine retour or normal consignment
        if (isset($_GET['retour']) && $_GET['retour'] == 'true') {
            $myparcel_plugin_action = 'verzending-aanmaken-retour/';
            $myparcel_action = 'retour';
        } else {
            $myparcel_plugin_action = 'verzending-aanmaken/';
            $myparcel_action = 'return';
        }

        $conf_url = Configuration::get('PS_SHOP_DOMAIN');
        if(empty($conf_url)) { $conf_url = $_SERVER['SERVER_NAME']; } // multistore compatibility

        $return_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $conf_url . $_SERVER['PHP_SELF'] . '?action=' . $myparcel_action . '&order_id=' . $_GET['order_id'] . '&timestamp=' . $_GET['timestamp'];

        $order = new OrderCore($_GET['order_id']);

        $customer = new CustomerCore($order->id_customer);

        $address = new AddressCore($order->id_address_delivery);

        $isoCode = CountryCore::getIsoById($address->id_country);

        if (in_array($isoCode, array('NL', null))) {
            $street = getAddressComponents($address->address1 . ', ' . $address->address2);
			
			/*----------------Since version 1.2.1----------------*/
			$pg_address = $myparcel->isPgAddress($address, $street);
            if ($pg_address) {
                $street['number_addition'] = '';
            }
			/*--------------------------------------------v1.2.1*/
            $consignment = array(
            	'ToAddress[country_code]'    => $isoCode,
            	'ToAddress[name]'            => $address->firstname . ' ' . $address->lastname,
            	'ToAddress[business]'        => $address->company,
            	'ToAddress[postcode]'        => $address->postcode,
            	'ToAddress[house_number]'    => $street['house_number'],
            	'ToAddress[number_addition]' => $street['number_addition'],
            	'ToAddress[street]'          => $street['street'],
            	'ToAddress[town]'            => $address->city,
            	'ToAddress[email]'           => $customer->email,
				'ToAddress[phone_number]'    => !empty($address->phone) ? $address->phone : $address->phone_mobile
				
            );
        } else {
            $weight = 0;

            $products = $order->getProducts();

            foreach ($products as $product) {
                $weight += $product['product_quantity'] * $product['product_weight'];
            }

            $consignment = array(
            	'ToAddress[country_code]' => $isoCode,
            	'ToAddress[name]'         => $address->firstname . ' ' . $address->lastname,
            	'ToAddress[business]'     => $address->company,
            	'ToAddress[street]'       => $address->address1 . ', ' . $address->address2,
            	'ToAddress[eps_postcode]' => $address->postcode,
            	'ToAddress[town]'         => $address->city,
            	'ToAddress[email]'        => $customer->email,
            	'ToAddress[phone_number]' => $address->phone,
            	'weight'                  => $weight,
            );
        }
?>
		<html>
		<body onload="document.getElementById('myparcel-create-consignment').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_URL . 'plugin/' . $myparcel_plugin_action . $_GET['order_id']; ?>/?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="myparcel-create-consignment"
                style="visibility:hidden;"
                >
<?php
        foreach ($consignment as $param => $value) {
            echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /**
     * PLUGIN POPUP RETURN CLOSE
     */
    if ($_GET['action'] == 'return' || $_GET['action'] == 'retour') {
        $order_id       = $_GET['order_id'];
        $timestamp      = $_GET['timestamp'];
        $consignment_id = $_GET['consignment_id'];
        $retour         = ($_GET['action'] == 'retour') ? 1 : 0;
        $tracktrace     = $_GET['tracktrace'];
        $postcode       = $_GET['postcode'];

        Db::getInstance()->insert(
            'myparcel',
            array(
                'order_id'       => $order_id,
                'consignment_id' => $consignment_id,
                'retour'         => $retour,
                'tracktrace'     => $tracktrace,
                'postcode'       => $postcode,
            )
        );
?>
		<html>
		<body onload="updateParentWindow();">
            <h4>Consignment <?php echo $consignment_id; ?> aangemaakt [<a href="<?php echo MYPARCEL_URL; ?>plugin/label/<?php echo $consignment_id; ?>">label bekijken</a>]</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Click here to close this window and return to webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow() {
                    if (!window.opener || !window.opener.MyParcel || !window.opener.MyParcel.PrestashopPlugin) {
                        alert('No connection with Prestashop webshop');
                        return;
                    }

                    document.getElementById('close-window').style.display = 'block';
                    window.opener.location.reload();
                    window.close();
                }
            </script>
        </body>
        </html>
<?php
        exit;
    }

    /**
     * PLUGIN POPUP PRINT
     */
    if ($_GET['action'] == 'print') {
        $consignments = $_GET['consignments'];
?>
		<html>
		<body onload="document.getElementById('myparcel-create-pdf').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_URL; ?>plugin/genereer-pdf"
                method="post"
                id="myparcel-create-pdf"
                style="visibility:hidden;"
                >
<?php
        echo '<input type="text" name="consignments" value="' . htmlspecialchars($consignments) . '" />';
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /**
     * PLUGIN BATCH CREATE
     */
    if ($_GET['action'] == 'process')
    {
        $conf_url = Configuration::get('PS_SHOP_DOMAIN');
        if(empty($conf_url)) { $conf_url = $_SERVER['SERVER_NAME']; } // multistore compatibility

        $return_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $conf_url . $_SERVER['PHP_SELF'] . '?action=batchreturn&timestamp=' . $_GET['timestamp'];

        $order_ids = (strpos($_GET['order_ids'], '|') !== false) ?
            explode('|', $_GET['order_ids']) : array($_GET['order_ids']);

        $formParams = array();

        foreach ($order_ids as $order_id) {
            $order = new OrderCore($order_id);

            $customer = new CustomerCore($order->id_customer);

            $address = new AddressCore($order->id_address_delivery);

            $isoCode = CountryCore::getIsoById($address->id_country);

            if (in_array($isoCode, array('NL', null))) {
                $street = getAddressComponents($address->address1 . ', ' . $address->address2);

				/*----------------Since version 1.2.1----------------*/
				$pg_address = $myparcel->isPgAddress($address, $street);
				if ($pg_address) {
					$street['number_addition'] = '';
				}
				/*--------------------------------------------v1.2.1*/

                $consignment = array(
                    'ToAddress' => array(
                    	'country_code'    => $isoCode,
                    	'name'            => $address->firstname . ' ' . $address->lastname,
                    	'business'        => $address->company,
                    	'postcode'        => $address->postcode,
                    	'house_number'    => $street['house_number'],
                    	'number_addition' => $street['number_addition'],
                    	'street'          => $street['street'],
                    	'town'            => $address->city,
                    	'email'           => $customer->email,
						'phone_number'    => !empty($address->phone) ? $address->phone : $address->phone_mobile,
                    )
                );
            } else {
                $weight = 0;

                $products = $order->getProducts();

                foreach ($products as $product) {
                    $weight += $product['product_quantity'] * $product['product_weight'];
                }

                $consignment = array(
                    'ToAddress' => array(
                    	'country_code' => $isoCode,
                    	'name'         => $address->firstname . ' ' . $address->lastname,
                    	'business'     => $address->company,
                    	'street'       => $address->address1 . ', ' . $address->address2,
                    	'eps_postcode' => $address->postcode,
                    	'town'         => $address->city,
                    	'email'        => $customer->email,
                    	'phone_number' => $address->phone,
                    ),
                    'weight' => $weight,
                );
            }

            $formParams[$order_id] = serialize($consignment);
        }
?>
		<html>
		<body onload="document.getElementById('myparcel-create-consignmentbatch').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_URL . 'plugin/verzending-batch'; ?>/?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="myparcel-create-consignmentbatch"
                style="visibility:hidden;"
                >
<?php
        foreach ($formParams as $param => $value) {
            echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /**
     * PLUGIN BATCH RETURN CLOSE
     */
    if ($_GET['action'] == 'batchreturn') {
        foreach ($_POST as $order_id => $serialized_data) {
            if (!is_numeric($order_id)) {
                continue;
            }

            $order = new OrderCore($order_id);

            if (null != $order->id_shop) {
                $data = unserialize($serialized_data);

                Db::getInstance()->insert(
                    'myparcel',
                    array(
                        'order_id'       => $order_id,
                        'consignment_id' => $data['consignment_id'],
                        'retour'         => null,
                        'tracktrace'     => $data['tracktrace'],
                        'postcode'       => $data['postcode'],
                    )
                );
            }
        }
?>
		<html>
		<body onload="updateParentWindow();">
            <h4>Consignments aangemaakt</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Click here to close this window and return to webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.MyParcel || !window.opener.MyParcel.PrestashopPlugin) {
                        alert('No connection with Prestashop webshop');
                        return;
                    }
                    document.getElementById('close-window').style.display = 'block';
                    window.opener.location.reload();
                    window.close();
                }
            </script>
        </body>
        </html>
<?php
        exit;
    }
	
	if ($_GET['action'] == 'save_pg_address')
    {
       /* if (version_compare(phpversion(), '5.4.0', '<')) {
            if (session_id() == '') session_start();
        } else {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }*/

        $data_array = array(
            //'order_id' => session_id(),
            'name' => pSQL($_GET['pg_extra_name']),
            'street' => pSQL($_GET['pg_extra_street']),
            'house_number' => pSQL($_GET['pg_extra_house_number']),
            'number_addition' => pSQL($_GET['pg_extra_number_addition']),
            'postcode' => pSQL($_GET['pg_extra_postcode']),
            'town' => pSQL($_GET['pg_extra_town']),
        );

        $results = Db::getInstance()->ExecuteS(
            sprintf(
                "SELECT * FROM '._DB_PREFIX_.'myparcel_pg_address WHERE `name` = '%s' AND `postcode`='%s'",
                $data_array['name'],
                $data_array['postcode']
            )
        );

        if (!empty($results)) {
            Db::getInstance()->update(
                'myparcel_pg_address',
                $data_array,
                'order_id="' . $session_id . '"'
            );
        } else {
            // Insert into myparcel_pg_address
            Db::getInstance()->insert('myparcel_pg_address', $data_array);
        }
        echo '1';
        die;
    }
}