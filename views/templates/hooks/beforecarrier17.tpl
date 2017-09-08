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
<div id="myparcel"></div>
<script type="text/javascript">
  (function () {
    var currentCarrier = 0;

    function carrierCheck() {
      var $deliveryOptions = $('.delivery-options');
      var idCarrier = parseInt($deliveryOptions.find('input:checked').val(), 10);
      if (!idCarrier || idCarrier === currentCarrier) {
        return;
      }
      currentCarrier = idCarrier;

      var target = '#hook-display-before-carrier';
      if ('{$widgetHook|escape:'javascript':'UTF-8'}' === 'extraCarrier') {
        target = '#extra_carrier';
      }

      var html = '<iframe name="myparcelcheckoutframe" id="myparcelcheckoutframe" src="{$myparcel_checkout_link|escape:'javascript':'UTF-8' nofilter}" width="100%" height="600px" frameBorder="0"></iframe>';

      var $myparcelcheckoutframe = $('#myparcelcheckoutframe');

      if ($myparcelcheckoutframe.length) {
        $myparcelcheckoutframe.replaceWith(html);
      } else {
        $(target).append(html);
      }
    }

    function scheduleCarrierListCheck() {
      setTimeout(checkCarrierList, 100);
    }

    function checkCarrierList() {
      // Only check the carrier list when we are at step 3
      var currentStep = parseInt($('.checkout-step.js-current-step').find('.step-number').text(), 10);
      if (currentStep !== 3) {
        return;
      }

      // PrestaShop lacks an event in its API that tells us whether the carrier was actually changed on the server
      // We can only wait for all XHR events to have finished and then start requesting the new hook HTML
      if ($.active) {
        $(document).ajaxStop(carrierCheck);
      } else {
        carrierCheck();
      }
    }

    function initMyParcelCheckFrame() {
      if (typeof $ === 'undefined' || typeof window.prestashop === 'undefined') {
        setTimeout(initMyParcelCheckFrame, 100);

        return;
      }

      $(document).ready(function () {
        var xhr = null;

        window.addEventListener('message', function (event) {
          if (event.data
            && event.data.messageOrigin === 'myparcelcheckout'
            && event.data.subject === 'height'
          ) {
            var newHeight = parseInt(event.data.height, 10);
            if (newHeight < 0) {
              $('#myparcelcheckoutframe').remove();
            } else {
              $('#myparcelcheckoutframe').height(parseInt(event.data.height, 10));
            }
          }
        });

        window.addEventListener('message', function (event) {
          if (event.data
            && event.data.messageOrigin === 'myparcelcheckout'
            && event.data.subject === 'selection_changed'
          ) {
            if (xhr) {
              xhr.abort();
            }

            var selection = event.data.selection;

            xhr = $.ajax({
              url: '{$myparcel_deliveryoptions_link|escape:'javascript':'UTF-8' nofilter}',
              type: 'post',
              data: {
                ajax: true,
                updateOption: true,
                deliveryOption: selection,
              },
              success: function (response) {
                $('.delivery-options').find('input:checked').trigger('change');

                if (typeof response !== 'undefined' && typeof response.carrier_data !== 'undefined') {
                  $.each(response.carrier_data, function (i, carrierData) {
                    $.each(carrierData, function (idCarrier, carrier) {
                      var price = 'Gratis';
                      if (!carrier.is_free) {
                        price = carrier.total_price_with_tax.toLocaleString(window.prestashop.language.language_code, { style: 'currency', currency: window.prestashop.currency.iso_code.toUpperCase()});
                      }

                      $('#delivery_option_' + parseInt(idCarrier, 10))
                        .closest('.delivery-option')
                        .find('.carrier-price')
                        .html(price);

                      $('#cart-subtotal-shipping')
                        .find('.value')
                        .html(price);
                    });
                  });
                }
              },
              error: function () {
                xhr = null;
              }
            });
          }
        });

        // Get scheduled after the PS update carrier events, again PS provides no API, so we can add a few short delays
        // that should force the browser to invoke these methods AFTER the PS update carrier events
        window.prestashop.on('updatedDeliveryForm', scheduleCarrierListCheck);
        window.prestashop.on('changedCheckoutStep', scheduleCarrierListCheck);

        checkCarrierList();
      });
    }

    initMyParcelCheckFrame();
  }());
</script>
