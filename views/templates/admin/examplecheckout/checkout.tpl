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
<!doctype html>
<html lang="{$language_code|escape:'html' nofilter}">
<head>
  <meta charset="UTF-8">
</head>
<body style="overflow-x: hidden; overflow-y: auto; margin: 8px">
  <div id="myparcelapp" class="myparcelcheckout"></div>
  <script type="text/javascript">
    (function () {
      window.addEventListener('message', function (event) {
        if (!event.data) {
          return;
        }

        try {
          var data = JSON.parse(event.data);
        } catch (e) {
          return;
        }

        if (data) {
          if (typeof window.checkout !== 'undefined'
            && typeof data === 'object'
            && data.subject === 'sendStyle'
          ) {
            window.checkout.setStyle(data.style);

            var newEvent = {
              subject: 'receivedStyle',
              style: data.style
            };
            event.source.postMessage(JSON.stringify(newEvent), event.origin);
          }
        }
      });

      (function initMyParcelCheckout() {
        if (typeof window.MyParcelModule === 'undefined'
          || typeof window.MyParcelModule.app === 'undefined'
          || typeof window.MyParcelModule.app.default === 'undefined'
          || typeof window.MyParcelModule.app.default.checkout === 'undefined'
        ) {
          setTimeout(initMyParcelCheckout, 100);

          return;
        }

        window.MyParcelModule.app.default.checkout().then(function (fn) {
          window.checkout = new fn.default(
            {
              data: {include file="./example.json"},
              target: 'myparcelapp',
              form: null,
              iframe: true,
              refresh: false,
              selected: null,
              street: 'Siriusdreef',
              houseNumber: '66',
              postalCode: '2132WT',
              deliveryDaysWindow: 12,
              dropOffDelay: 0,
              dropOffDays: '1,2,3,4,5',
              cutoffTime: '15:30:00',
              cc: 'NL',
              capabilities: {
                delivery: true,
                pickup: true,
                pickupExpress: true,
                deliveryMorning: true,
                deliveryEvening: true,
                signature: true,
                onlyRecipient: true,
                signatureOnlyRecipient: true,
                deliveryMonday: true,
              },
              pricing: {
                delivery: 0,
                deliveryMorning: 2,
                deliveryEvening: 2,
                signature: 2,
                onlyRecipient: 2,
                signatureOnlyRecipient: 2,
                pickup: 0,
                pickupExpress: 0
              },
              signaturePreferred: {if $signaturePreferred}true{else}false{/if},
              onlyRecipientPreferred: {if $onlyRecipientPreferred}true{else}false{/if},
              currencyFormat: '{MyParcel::getCurrencyFormat()|escape:'javascript':'UTF-8'}',
              numberFormat: '{MyParcel::getNumberFormat()|escape:'javascript':'UTF-8'}',
              urls: {
                deliveryOptions: '',
                updateSelection: ''
              },
              carrierId: 1,
              carrierCode: 'myparcelnl_postnl'
            },
            {include file="../../front/translations.tpl"},
            {
              foreground1Color: '{$foreground1Color|escape:'javascript'}' || '#FFFFFF',
              foreground2Color: '{$foreground2Color|escape:'javascript'}' || '#000000',
              foreground3Color: '{$foreground3Color|escape:'javascript'}' || '#000000',
              background1Color: '{$background1Color|escape:'javascript'}' || '#FBFBFB',
              background2Color: '{$background2Color|escape:'javascript'}' || '#01BBC5',
              background3Color: '{$background3Color|escape:'javascript'}' || '#75D3D8',
              highlightColor: '{$highlightColor|escape:'javascript'}' || '#FF8C00',
              inactiveColor: '{$inactiveColor|escape:'javascript'}' || '#848484',
              fontFamily: '{$fontFamily|escape:'javascript'}' || 'Exo',
              fontSize: 2,
            }
          );
        });
      }());
    })();
  </script>
  {include file="../../hook/load_webpack_chunks.tpl"}
</body>
</html>
