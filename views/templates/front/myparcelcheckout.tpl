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
<!doctype html>
<html>
<body>
<div id="myparcelapp" class="myparcelcheckout"></div>
<script type="text/javascript" src="{$checkoutJs|escape:'htmlall':'UTF-8' nofilter}"></script>
<script type="text/javascript">
  (function () {
    function initMyParcelCheckout() {
      if (typeof MyParcelCheckout === 'undefined') {
        setTimeout(initMyParcelCheckout, 100);

        return;
      }

      window.myparcelLocale = '{Context::getContext()->language->iso_code|strtolower|escape:'javascript':'UTF-8'}';

      window.checkout = new MyParcelCheckout({
        target: 'myparcelapp',
        form: null,
        iframe: true,
        refresh: false,
        selected: null,
        street: '{$streetName|escape:'javascript':'UTF-8' nofilter}',
        houseNumber: '{$houseNumber|escape:'javascript':'UTF-8' nofilter}',
        postalCode: '{$postcode|escape:'javascript':'UTF-8' nofilter}',
        deliveryDaysWindow: {$deliveryDaysWindow|intval nofilter},
        dropoffDelay: {$dropoffDelay|intval nofilter},
        dropoffDays: '{$dropoffDays|escape:'javascript':'UTF-8' nofilter}',
        cutoffTime: '{if $cutoffTime}{$cutoffTime|escape:'javascript':'UTF-8' nofilter}:00{else}15:30:00{/if}',
        cc: '{$countryIso|escape:'javascript':'UTF-8'}',
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
          foreground1Color: '{$foreground1color|escape:'javascript':'UTF-8' nofilter}',
          foreground2Color: '{$foreground2color|escape:'javascript':'UTF-8' nofilter}',
          background1Color: '{$background1color|escape:'javascript':'UTF-8' nofilter}',
          background2Color: '{$background2color|escape:'javascript':'UTF-8' nofilter}',
          background3Color: '{$background3color|escape:'javascript':'UTF-8' nofilter}',
          highlightColor: '{$highlightcolor|escape:'javascript':'UTF-8' nofilter}',
          fontFamily: '{$fontfamily|escape:'javascript':'UTF-8' nofilter}'
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
        baseUrl: '{$myparcel_ajax_checkout_link|escape:'javascript':'UTF-8' nofilter}',
        locale: '{MyParcel::getLocale()|escape:'javascript':'UTF-8'}',
        currency: '{$currencyIso|escape:'javascript':'UTF-8'}',
        translations: {
          'en-US': {
            morning: 'Ochtend',
            standard: 'Standaard',
            night: 'Avond',
            signed: 'Handtekening voor ontvangst',
            recipientOnly: 'Recipient only',
            noAddress: 'Geen adres opgegeven',
            deliveredAtHomeOrOffice: 'Thuis afgeleverd of op het werk',
            deliveryOptions: 'Bezorgopties',
            pickupAtPostNL: 'Ophalen op een PostNL postkantoor',
            from1600: 'From 4pm',
            from830: 'From 8:30am',
            pickup: 'Ophalen'
          },
          'nl-NL': {
            morning: 'Ochtend',
            standard: 'Standaard',
            night: 'Avond',
            signed: 'Handtekening voor ontvangst',
            recipientOnly: 'Alleen geadresseerde',
            noAddress: 'Geen adres opgegeven',
            deliveredAtHomeOrOffice: 'Thuis afgeleverd of op het werk',
            deliveryOptions: 'Bezorgopties',
            pickupAtPostNL: 'Ophalen op een PostNL postkantoor',
            from1600: 'From 4pm',
            from830: 'From 8:30am',
            pickup: 'Ophalen'
          }
        }
      });
    }

    initMyParcelCheckout();
  })();
</script>
</body>
</html>
