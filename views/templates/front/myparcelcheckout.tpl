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
  {if !in_array($fontFamily, ['Arial', 'Comic Sans MS', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana'])}
    <link href="https://fonts.googleapis.com/css?family={$fontFamily|escape:'javascript' nofilter}:400,700" rel="stylesheet">
  {/if}
  <style>
    #myparcelapp {
      width: 1px;
      min-width: 100%;
      *width: 100%;
    }

    @keyframes animation-14kwaps {
      0%, 80%, 100% {
        -webkit-transform: scale(0);
        -ms-transform: scale(0);
        transform: scale(0);
      }
      40% {
        -webkit-transform: scale(1.0);
        -ms-transform: scale(1.0);
        transform: scale(1.0);
      }
    }

    .no-options-container {
      display: table;
      min-height: 200px;
      height: 100%;
      width: 100%;
      font-weight: 700;
      font-family: "{$fontFamily|escape:'htmlall':'UTF-8' nofilter}";
      color: {$foreground1Color|escape:'htmlall':'UTF-8' nofilter};
      background-color: {$background2Color|escape:'htmlall':'UTF-8' nofilter};
    }

    .no-options {
      padding: 10px;
      display: table-cell;
      vertical-align: middle;
      text-align: center;
      font-size: {if $fontSize == 1}18{elseif $fontSize == 2}24{else}30{/if}px;
    }

    .spinner-div {
      margin: 20px auto;
    }

    .spinner-div div {
      width: 18px;
      height: 18px;
      background-color: {$foreground1Color|escape:'htmlall':'UTF-8' nofilter};
      border-radius: 100%;
      display: inline-block;
      -webkit-animation: animation-14kwaps 1.4s infinite ease-in-out both;
      animation: animation-14kwaps 1.4s infinite ease-in-out both;
    }

    .spinner-div .bounce1 {
      -webkit-animation-delay: 0s;
      animation-delay: 0s;
      background-color: #fff;
      margin: 0 2px;
    }
    .spinner-div .bounce2 {
      -webkit-animation-delay: -0.16s;
      animation-delay: -0.16s;
      background-color: #fff;
      margin: 0 2px;
    }
    .spinner-div .bounce3 {
      -webkit-animation-delay: -0.32s;
      animation-delay: -0.32s;
      background-color: #fff;
      margin: 0 2px;
    }
  </style>
</head>
<body style="margin: 0">
  <div id="myparcelapp" class="myparcelcheckout">
    <div class="no-options-container">
      <div class="no-options">
        <div class="spinner-div">
          <div class="bounce3"></div>
          <div class="bounce2"></div>
          <div class="bounce1"></div>
        </div>
        <span>{l s='Loading delivery options' mod='myparcel'}...</span></div>
    </div>
  </div>
  <script type="text/javascript">
    window.priceDisplayPrecision = {$smarty.const._PS_PRICE_DISPLAY_PRECISION_|intval nofilter};
    window.currency_iso_code = '{Context::getContext()->currency->iso_code|escape:'htmlall':'UTF-8'}';
    window.currencySign = '{Context::getContext()->currency->sign|escape:'javascript':'UTF-8'}';
    window.currencyFormat = {Context::getContext()->currency->format|intval} || 3;
    window.currencyBlank = {Context::getContext()->currency->blank|intval};
  </script>
  <script type="text/javascript" src="{$base_dir_ssl|escape:'htmlall':'UTF-8' nofilter}js/jquery/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="{$base_dir_ssl|escape:'htmlall':'UTF-8' nofilter}js/tools.js"></script>
  <script type="text/javascript">
    (function () {
      window.MyParcelModule = window.MyParcelModule || {ldelim}{rdelim};
      window.MyParcelModule.misc = window.MyParcelModule.misc || {ldelim}{rdelim};
      window.MyParcelModule.misc.errorCodes = {
        '3212': '{l s='Unknown address' mod='myparcel' js=1}'
      };
      window.MyParcelModule.debug = {if $mpLogApi}true{else}false{/if};
      window.MyParcelModule.misc.mondayDelivery = {if Configuration::get(MyParcel::MONDAY_DELIVERY_SUPPORTED)}true{else}false{/if};

      top.postMessage(JSON.stringify({
        messageOrigin: 'myparcelcheckout',
        subject: 'height',
        height: document.getElementById('myparcelapp').scrollHeight + 80,
      }), '*');

      function initMyParcelCheckout() {
        if (typeof window.MyParcelModule === 'undefined'
          || typeof window.MyParcelModule.app === 'undefined'
          || typeof window.MyParcelModule.app.default === 'undefined'
          || typeof window.MyParcelModule.app.default.checkout === 'undefined'
        ) {
          return setTimeout(initMyParcelCheckout, 100);
        }

        window.checkout = window.MyParcelModule.app.default.checkout().then(function (fn) {
          new fn.default({
              target: 'myparcelapp',
              form: null,
              iframe: true,
              refresh: false,
              selected: null,
              street: '{$streetName|escape:'javascript':'UTF-8' nofilter}',
              houseNumber: '{$houseNumber|escape:'javascript':'UTF-8' nofilter}',
              postalCode: '{$postcode|escape:'javascript':'UTF-8' nofilter}',
              deliveryDaysWindow: {$deliveryDaysWindow|intval nofilter},
              dropOffDelay: {$dropOffDelay|intval nofilter},
              dropOffDays: '{$dropOffDays|escape:'javascript':'UTF-8' nofilter}',
              cutoffTime: '{if $cutoffTime}{$cutoffTime|escape:'javascript':'UTF-8' nofilter}:00{else}15:30:00{/if}',
              cacheKey: '{$cacheKey|escape:'htmlall':'UTF-8' nofilter}',
              cc: '{$countryIso|escape:'javascript':'UTF-8'|strtoupper nofilter}',
              signaturePreferred: {if $signaturePreferred}true{else}false{/if},
              onlyRecipientPreferred: {if $onlyRecipientPreferred}true{else}false{/if},
              capabilities: {
                delivery: {if $delivery}true{else}false{/if},
                pickup: {if $pickup}true{else}false{/if},
                pickupExpress: {if $express}true{else}false{/if},
                deliveryMorning: {if $morning}true{else}false{/if},
                deliveryEvening: {if $evening}true{else}false{/if},
                signature: {if $signature}true{else}false{/if},
                onlyRecipient: {if $onlyRecipient}true{else}false{/if},
                signatureOnlyRecipient: {if $signatureOnlyRecipient}true{else}false{/if},
                cooledDelivery: {if $cooledDelivery}true{else}false{/if},
                ageCheck: {if $ageCheck}true{else}false{/if},
              },
              pricing: {
                delivery: 0,
                deliveryMorning: {$morningFeeTaxIncl|floatval nofilter},
                deliveryEvening: {$eveningFeeTaxIncl|floatval nofilter},
                signature: {$signatureFeeTaxIncl|floatval nofilter},
                onlyRecipient: {$onlyRecipientFeeTaxIncl|floatval nofilter},
                signatureOnlyRecipient: {$signatureOnlyRecipientFeeTaxIncl|floatval nofilter},
                pickup: 0,
                pickupExpress: {$morningPickupFeeTaxIncl|floatval nofilter}
              },
              urls: {
                deliveryOptions: '{$myparcel_ajax_checkout_link|escape:'javascript' nofilter}',
                selectionChanged: ''
              },
              locale: 'nl-NL',
              currencyFormat: '{MyParcel::getCurrencyFormat()|escape:'javascript':'UTF-8'}',
              numberFormat: '{MyParcel::getNumberFormat()|escape:'javascript':'UTF-8'}',
              carrierId: 1,
              carrierCode: 'myparcelnl_postnl',
            },
            {include file="./translations.tpl"},
            {
              foreground1Color: '{$foreground1Color|escape:'javascript':'UTF-8' nofilter}',
              foreground2Color: '{$foreground2Color|escape:'javascript':'UTF-8' nofilter}',
              foreground3Color: '{$foreground3Color|escape:'javascript':'UTF-8' nofilter}',
              background1Color: '{$background1Color|escape:'javascript':'UTF-8' nofilter}',
              background2Color: '{$background2Color|escape:'javascript':'UTF-8' nofilter}',
              background3Color: '{$background3Color|escape:'javascript':'UTF-8' nofilter}',
              highlightColor: '{$highlightcolor|escape:'javascript':'UTF-8' nofilter}',
              inactiveColor: '{$inactivecolor|escape:'javascript':'UTF-8' nofilter}',
              fontFamily: '{$fontFamily|escape:'javascript':'UTF-8' nofilter}',
              fontSize: {$fontSize|intval} ? {$fontSize|intval} : 2,
            }
          );
        });

        top.postMessage(JSON.stringify({
          messageOrigin: 'myparcelcheckout',
          subject: 'height',
          height: document.getElementById('myparcelapp').scrollHeight + 80,
        }), '*');
      }

      initMyParcelCheckout();
    })();
  </script>
  {include file="../hook/load_webpack_chunks.tpl"}
</body>
</html>
