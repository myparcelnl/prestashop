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

    $address = str_replace(array('?', '*', '[', ']', ',', '!'), ' ', $address);
    $address = preg_replace('/\s\s+/', ' ', $address);

    preg_match('/^([0-9]*)(.*?)([0-9]+)(.*)/', $address, $matches);

    if (!empty($matches[2])) {
        $ret['street'] = trim($matches[1] . $matches[2]);
        $ret['house_number'] = trim($matches[3]);
        $ret['number_addition'] = trim($matches[4]);
    } else {
        $ret['street'] = $address;
    }

    return $ret;
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

        if (count($consignments) > 0) {
            $statusFile = file(MYPARCEL_URL . 'status/tnt/' . implode('|', $consignments));

            foreach($statusFile as $row) {
                $row = explode('|', $row);

                if (count($row) != 3) {
                    exit;
                }

                Db::getInstance()->update(
                    'myparcel',
                    array(
                    	'tnt_status'     => trim($row[2]),
                    	'tnt_updated_on' => date('Y-m-d H:i:s'),
                        'tnt_final'      => (int) $row[1],
                    ),
                    '`consignment_id` = "' . $row[0] . '"'
                );
            }
        }
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
}