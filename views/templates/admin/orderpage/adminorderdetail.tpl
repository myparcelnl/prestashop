{*
 * 2017-2018 DM Productions B.V.
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
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div id="myparcel-export-panel"></div>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8' nofilter}views/js/app/dist/orderpage-5d9567971fcaaffa.bundle.min.js"></script>
<script type="text/javascript">
  (function () {
    function initAdminOrderDetail() {
      if (typeof $ === 'undefined' || typeof MyParcelModule === 'undefined') {
        setTimeout(initAdminOrderDetail, 10);

        return;
      }

      window.MyParcelModule.misc = window.MyParcelModule.misc || {ldelim}{rdelim};
      window.MyParcelModule.misc.process_url = '{$myparcelProcessUrl|escape:'javascript':'UTF-8' nofilter}';
      window.MyParcelModule.misc.module_url = '{$myparcel_module_url|escape:'javascript':'UTF-8' nofilter}';
      window.MyParcelModule.misc.countries = {$jsCountries|json_encode};
      window.MyParcelModule.invoiceSuggestion = '{$invoiceSuggestion|escape:'javascript':'UTF-8' nofilter}';
      window.MyParcelModule.weightSuggestion = '{$weightSuggestion|escape:'javascript':'UTF-8' nofilter}';
      try {
        window.MyParcelModule.paperSize = {$papersize|json_encode};
      } catch (e) {
        window.MyParcelModule.paperSize = false;
      }
      window.MyParcelModule.debug = {if Configuration::get(MyParcel::LOG_API)}true{else}false{/if};

        new window.MyParcelModule.orderpage(
          {$idOrder|intval nofilter},
          JSON.parse('{$concept|escape:'javascript':'UTF-8' nofilter}'),
          JSON.parse('{$preAlerted|escape:'javascript':'UTF-8' nofilter}'),
          {include file="../translations.tpl"}
        );
    }

    initAdminOrderDetail();
  })();
</script>
