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
<script type="text/javascript">
  (function () {
    function initMyParcelExport() {
      if (typeof window.MyParcelModule === 'undefined'
        || typeof window.MyParcelModule.back === 'undefined'
        || typeof window.MyParcelModule.back.ordergrid === 'undefined'
      ) {
        setTimeout(initMyParcelExport, 10);

        return;
      }

      function documentReady(fn) {
        if (document.readyState !== 'loading'){
          fn();
        } else if (document.addEventListener) {
          document.addEventListener('DOMContentLoaded', fn);
        } else {
          document.attachEvent('onreadystatechange', function() {
            if (document.readyState !== 'loading')
              fn();
          });
        }
      }

      documentReady(function () {
        window.MyParcelModule.misc = window.MyParcelModule.misc || {ldelim}{rdelim};
        window.MyParcelModule.misc.process_url = '{$mpProcessUrl|escape:'javascript'}';
        window.MyParcelModule.misc.module_url = '{$mpModuleDir|escape:'javascript'}';
        window.MyParcelModule.misc.countries = {mypa_json_encode($mpJsCountries)};
        window.MyParcelModule.misc.icons = [];
        try {
          window.MyParcelModule.paperSize = {mypa_json_encode($mpPaperSize)};
        } catch (e) {
          window.MyParcelModule.paperSize = false;
        }
        window.MyParcelModule.askPaperSize = {if $mpAskPaperSize}true{else}false{/if};
        window.MyParcelModule.askReturnConfig = {if $mpAskReturnConfig}true{else}false{/if};
        window.MyParcelModule.debug = {if $mpLogApi}true{else}false{/if};
        window.MyParcelModule.currency = {
          blank: '{$mpCurrency->blank|escape:'javascript':'UTF-8'}',
          format: '{$mpCurrency->format|escape:'javascript':'UTF-8'}',
          sign: '{$mpCurrency->sign|escape:'javascript':'UTF-8'}',
          iso_code: '{$mpCurrency->iso_code|escape:'javascript':'UTF-8'}'
        };

        if (!window.MyParcelModule.paperSize) {
          window.MyParcelModule.paperSize = {
            size: 'standard',
            labels: {
              1: true,
              2: true,
              3: true,
              4: true
            }
          };
        }

        new window.MyParcelModule.back.ordergrid(
          {include file="../translations.tpl"},
          {
            insurance: {$mpReturnInsuranceAmount|intval},
            recipientOnly: {if $mpRecipientOnly}true{else}false{/if},
            signature: {if $mpSignature}true{else}false{/if},
            extraLarge: {if $mpExtraLarge}true{else}false{/if},
            returnUndeliverable: {if $mpReturnUndeliverable}true{else}false{/if},
          }
        );
      });
    }

    {if $mpCheckWebhooks}
      var webhooksRequest = new XMLHttpRequest();
      webhooksRequest.open('GET', '{$mpProcessUrl|escape:'javascript'}&action=CheckWebhooks', true);
      webhooksRequest.send();
      webhooksRequest = null;
    {/if}
    initMyParcelExport();
  }());
</script>
