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
<!doctype html>
<html lang="{$language_code|escape:'html' nofilter}">
<head>
  <style>
    #myparcelapp {
      width: 1px;
      min-width: 100%;
      *width: 100%;
    }
  </style>
</head>
<body>
  <div id="myparcelapp" class="myparcelcheckout"></div>
  <script type="text/javascript">
    {if $smarty.const._TB_VERSION_}
    window.currencyModes = {mypa_json_encode(Currency::getModes())};
    {/if}
    window.priceDisplayPrecision = {$smarty.const._PS_PRICE_DISPLAY_PRECISION_|intval nofilter};
    window.currency_iso_code = '{Context::getContext()->currency->iso_code|escape:'htmlall'}';
    window.currencySign = '{Context::getContext()->currency->sign|escape:'javascript'}';
    window.currencyFormat = {Context::getContext()->currency->format|intval} || 3;
    window.currencyBlank = {Context::getContext()->currency->blank|intval};
  </script>
  <script type="text/javascript"></script>
  <script type="text/javascript" src="{$base_dir_ssl|escape:'htmlall' nofilter}js/jquery/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="{$base_dir_ssl|escape:'htmlall' nofilter}js/tools.js"></script>
  <script type="text/javascript">
    (function () {
      window.MyParcelModule = window.MyParcelModule || {ldelim}{rdelim};
      window.MyParcelModule.misc = window.MyParcelModule.misc || {ldelim}{rdelim};
      window.MyParcelModule.misc.errorCodes = {
        '3212': '{l s='Unknown address' mod='myparcel' js=1}'
      };
      window.MyParcelModule.debug = {if $mpLogApi}true{else}false{/if};
      window.MyParcelModule.async = {if $mpAsync}true{else}false{/if};
      window.MyParcelModule.misc.mondayDelivery = {if Configuration::get(MyParcel::MONDAY_DELIVERY_SUPPORTED)}true{else}false{/if};

      function initMyParcelCheckout() {
        if (typeof window.MyParcelModule === 'undefined'
          || typeof window.MyParcelModule.front === 'undefined'
          || typeof window.MyParcelModule.front.checkout === 'undefined'
        ) {
          setTimeout(initMyParcelCheckout, 100);

          return;
        }

        window.checkout = new window.MyParcelModule.front.checkout({
          target: 'myparcelapp',
          form: null,
          iframe: true,
          refresh: false,
          selected: null,
          street: '{$streetName|escape:'javascript' nofilter}',
          houseNumber: '{$houseNumber|escape:'javascript' nofilter}',
          postalCode: '{$postcode|escape:'javascript' nofilter}',
          deliveryDaysWindow: {$deliveryDaysWindow|intval nofilter},
          dropoffDelay: {$dropoffDelay|intval nofilter},
          dropoffDays: '{$dropoffDays|escape:'javascript' nofilter}',
          cutoffTime: '{if $cutoffTime}{$cutoffTime|escape:'javascript' nofilter}:00{else}15:30:00{/if}',
          cacheKey: '{$cacheKey|escape:'htmlall'}',
          cc: '{$countryIso|escape:'javascript' nofilter}',
          signedPreferred: {if $signedPreferred}true{else}false{/if},
          recipientOnlyPreferred: {if $recipientOnlyPreferred}true{else}false{/if},
          methodsAvailable: {
            timeframes: {if $delivery}true{else}false{/if},
            pickup: {if $pickup}true{else}false{/if},
            expressPickup: {if $express}true{else}false{/if},
            morning: {if $morning}true{else}false{/if},
            night: {if $night}true{else}false{/if},
            signed: {if $signed}true{else}false{/if},
            recipientOnly: {if $recipientOnly}true{else}false{/if},
            signedRecipientOnly: {if $signedRecipientOnly}true{else}false{/if}
          },
          customStyle: {
            foreground1Color: '{$foreground1color|escape:'javascript' nofilter}',
            foreground2Color: '{$foreground2color|escape:'javascript' nofilter}',
            foreground3Color: '{$foreground3color|escape:'javascript' nofilter}',
            background1Color: '{$background1color|escape:'javascript' nofilter}',
            background2Color: '{$background2color|escape:'javascript' nofilter}',
            background3Color: '{$background3color|escape:'javascript' nofilter}',
            highlightColor: '{$highlightcolor|escape:'javascript' nofilter}',
            inactiveColor: '{$inactivecolor|escape:'javascript' nofilter}',
            fontFamily: '{$fontFamily|escape:'javascript' nofilter}',
            fontSize: {$fontSize|intval} ? {$fontSize|intval} : 2
          },
          price: {
            morning: {$morningFeeTaxIncl|floatval nofilter},
            standard: 0,
            night: {$nightFeeTaxIncl|floatval nofilter},
            signed: {$signedFeeTaxIncl|floatval nofilter},
            recipientOnly: {$recipientOnlyFeeTaxIncl|floatval nofilter},
            signedRecipientOnly: {$signedRecipientOnlyFeeTaxIncl|floatval nofilter},
            pickup: 0,
            expressPickup: {$morningPickupFeeTaxIncl|floatval nofilter}
          },
          baseUrl: '{$myparcel_ajax_checkout_link|escape:'javascript' nofilter}',
          locale: 'nl-NL',
          currency: '{$currencyIso|escape:'javascript' nofilter}'
        },
          {include file="./translations.tpl"}
        );

        top.postMessage(JSON.stringify({
          messageOrigin: 'myparcelcheckout',
          subject: 'height',
          height: 300,
        }), '*');
      }

      initMyParcelCheckout();
    })();
  </script>
  <script type="text/javascript" src="{$mypaCheckoutJs|escape:'htmlall' nofilter}"></script>
</body>
</html>
