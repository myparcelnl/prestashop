{*
 * 2017 DM Productions B.V.
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
 * @author     DM Productions B.V. <info@dmp.nl>
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2017 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<script type="text/javascript">
	if (typeof window.mypa === 'undefined') {
		window.mypa = {ldelim}{rdelim};
	}
	window.mypa.myparcel_process_url = '{$myparcel_process_url|escape:'javascript':'UTF-8'}';
	window.mypa.api_key = '{$apiKey|escape:'javascript':'UTF-8'}';
	window.mypa.countries = {$jsCountries};
	window.mypa.icons = [];

	$(document).ready(function() {
		new MyParcelExport();
	});
</script>
