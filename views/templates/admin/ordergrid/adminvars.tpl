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
        || typeof window.MyParcelModule.app === 'undefined'
        || typeof window.MyParcelModule.app.default === 'undefined'
        || typeof window.MyParcelModule.app.default.orderGrid === 'undefined'
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
        window.MyParcelModule.misc.icons = [];

        window.MyParcelModule.app.default.orderGrid().then(function (fn) {
          new fn.default(
            {
              defaultReturnConfig: {
                insurance: {$mpReturnInsuranceAmount|intval},
                onlyRecipient: {if $mpOnlyRecipient}true{else}false{/if},
                signature: {if $mpSignature}true{else}false{/if},
                largeFormat: {if $mpExtraLarge}true{else}false{/if},
                returnUndeliverable: {if $mpReturnUndeliverable}true{else}false{/if}
              },
              returnCountries: ['NL', 'BE', 'DE', 'GB', 'UK', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LU', 'AT', 'SI', 'SK', 'ES', 'CZ'],
              defaultPaperFormat: {mypa_json_encode(MyParcel::getPaperFormat())},
              numberFormat: '{MyParcel::getNumberFormat()|escape:'javascript':'UTF-8'}',
              currencyFormat: '{MyParcel::getCurrencyFormat()|escape:'javascript':'UTF-8'}',
              urls: {
                assets: '{$mpModuleDir|escape:'javascript':'UTF-8'}',
                createLabel: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'createLabel'])|escape:'javascript':'UTF-8'}',
                createRelatedReturnLabel: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'createRelatedReturnLabel'])|escape:'javascript':'UTF-8'}',
                saveConceptData: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'saveConceptData'])|escape:'javascript':'UTF-8'}',
                orderInfo: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'orderInfo'])|escape:'javascript':'UTF-8':'UTF-8'}',
                refreshLabel: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'getShipment'])|escape:'javascript':'UTF-8'}',
                trackTrace: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'getShipmentHistory'])|escape:'javascript':'UTF-8'}',
                printLabel: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'printLabel'])|escape:'javascript':'UTF-8'}',
                deleteShipment: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'deleteShipment'])|escape:'javascript':'UTF-8'}',
                deliveryOptions: '{MyParcel::appendQueryToUrl($mpProcessUrl, ['action' => 'deliveryOptions'])|escape:'javascript':'UTF-8'}',
                goodsNomenclatureInstall: '{$mpGoodsNomenclatureInstallUrl|escape:'javascript':'UTF-8'}',
                goodsNomenclatureSearch: '{$mpGoodsNomenclatureSearchUrl|escape:'javascript':'UTF-8'}',
                goodsNomenclatureBrowse: '{$mpGoodsNomenclatureBrowseUrl|escape:'javascript':'UTF-8'}',
                goodsNomenclatureNavigate: '{$mpGoodsNomenclatureNavigateUrl|escape:'javascript':'UTF-8'}',
              },
              askPaperFormat: {if $mpAskPaperSize}true{else}false{/if},
              askReturnConfig: {if $mpAskReturnConfig}true{else}false{/if},
              carrierId: 1,
              carrierCode: 'myparcelnl_postnl',
              theme: '{MyParcel::getThemeVersion()|escape:'javascript':'UTF-8'}',
              goodsNomenclatureInstallStep: {if MyParcelGoodsNomenclature::isInstalled()}0{else}1{/if},
            },
            {include file="../translations.tpl"},
            {mypa_json_encode($mpJsCountries)},
            {
              onlyRecipient: {
                currency: 'EUR',
                amount: 30
              },
              signature: {
                currency: 'EUR',
                amount: 38
              },
              largeFormat: {
                NL: {
                  currency: 'EUR',
                  amount: 295
                },
                DE: {
                  currency: 'EUR',
                  amount: 295
                },
                BE: {
                  currency: 'EUR',
                  amount: 295
                },
                UK: {
                  currency: 'EUR',
                  amount: 295
                },
                GB: {
                  currency: 'EUR',
                  amount: 295
                },
                FR: {
                  currency: 'EUR',
                  amount: 295
                },
                ES: {
                  currency: 'EUR',
                  amount: 295
                }
              },
              onlyRecipientSignature: {
                currency: 'EUR',
                amount: 47
              },
              digitalStamp: {
                '0': {
                  currency: 'EUR',
                  amount: 83
                },
                '20': {
                  currency: 'EUR',
                  amount: 166
                },
                '50': {
                  currency: 'EUR',
                  amount: 249
                },
                '100': {
                  currency: 'EUR',
                  amount: 332
                },
                '350': {
                  currency: 'EUR',
                  amount: 415
                }
              },
              insurance: {
                '100': {
                  currency: 'EUR',
                  amount: 60
                },
                '250': {
                  currency: 'EUR',
                  amount: 100
                },
                '500': {
                  currency: 'EUR',
                  amount: 165
                },
                'm500': {
                  currency: 'EUR',
                  amount: 165
                }
              },
              ageCheck: {
                currency: 'EUR',
                amount: 150
              }
            }
          );
        });
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
