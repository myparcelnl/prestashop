{*
 * 2017-2019 DM Productions B.V.
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
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<button id="myparcel-update" type="button" class="btn btn-warning warn button"><i class="icon icon-cloud-download"></i> {l s='Update this module' mod='myparcel'}</button>
<script type="text/javascript">
  (function () {
    window.MyParcelModule = window.MyParcelModule || {ldelim}{rdelim};
    window.MyParcelModule.urls = window.MyParcelModule.urls || {ldelim}{rdelim};
    window.MyParcelModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8' nofilter}';
  }());
  (function initMyParcelUpdater() {
    if (typeof window.MyParcelModule === 'undefined'
      || typeof window.MyParcelModule.app === 'undefined'
      || typeof window.MyParcelModule.app.default === 'undefined'
      || typeof window.MyParcelModule.app.default.updater === 'undefined'
    ) {
      return setTimeout(initMyParcelUpdater, 100);
    }

    window.MyParcelModule.app.default.updater().then(function (fn) {
      fn.default(
        document.getElementById('myparcel-update'),
        {
          endpoint: '{$updateEndpoint|escape:'javascript':'UTF-8'}',
        },
        {
          error: '{l s='Error' mod='myparcel' js=1}',
          unableToConnect: '{l s='Unable to connect' mod='myparcel' js=1}',
          unableToUnzip: '{l s='Unable to unzip new module' mod='myparcel' js=1}',
          updated: '{l s='The module has been updated!' mod='myparcel' js=1}',
        }
      );
    });
  }());
</script>
