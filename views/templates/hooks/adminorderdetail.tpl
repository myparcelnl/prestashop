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
<div id="myparcel-export-panel"></div>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8' nofilter}views/js/myparcelpanel/dist/myparcelpanel.js"></script>
<script type="text/javascript">
  (function () {
    function initAdminOrderDetail() {
      if (typeof $ === 'undefined' || typeof MyParcelPanel === 'undefined') {
        setTimeout(initAdminOrderDetail, 100);

        return;
      }

      $(document).ready(function () {
        if (typeof window.mypa === 'undefined') {
          window.mypa = {ldelim}{rdelim};
        }
        window.mypa.myparcel_process_url = '{$myparcelProcessUrl|escape:'javascript':'UTF-8' nofilter}';
        window.mypa.countries = {$jsCountries};

        new MyParcelPanel(
                {$idOrder|intval},
          JSON.parse('{$concept|escape:'javascript':'UTF-8' nofilter}'),
          JSON.parse('{$preAlerted|escape:'javascript':'UTF-8' nofilter}')
        );
      });
    }

    initAdminOrderDetail();
  })();
</script>
