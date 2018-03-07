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
<iframe name="myparcelcheckoutframe"
        id="myparcelcheckoutframe"
        src="{$link->getModuleLink('myparcel', 'myparcelcheckout', array(), Tools::usingSecureMode())|escape:'htmlall':'UTF-8'}"
        width="100%"
        height="0"
        frameBorder="0">
</iframe>
<div id="myparcel"></div>
<script type="text/javascript">
  (function () {
    window.mypaNotified = window.mypaNotified || false;
    var pendingXhrs = [];

    function documentReady(fn) {
      if (document.readyState !== 'loading') {
        fn();
      } else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn);
      } else {
        document.attachEvent('onreadystatechange', function () {
          if (document.readyState !== 'loading') {
            fn();
          }
        });
      }
    }

    function getIdCarrier() {
      try {
        var idAddressSelected = window.prestashop.cart.id_address_delivery;

        var options = document.querySelectorAll('input[name="delivery_option[' + idAddressSelected + ']"]');
        var idCarrier = 0;
        options.forEach(function (radio) {
          if (radio.checked) {
            idCarrier = parseInt(radio.value.trim(',.'), 10);

            return false;
          }
        });

        return idCarrier;
      } catch (e) {
        return 0;
      }
    }

    function refreshIframe() {
      document.getElementById('myparcelcheckoutframe').src = document.getElementById('myparcelcheckoutframe').src;
    }

    function refreshSummary() {
      try {
        var refreshUrl = '{url entity='order' params=['ajax' => 1, 'action' => 'selectDeliveryOption']}';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', refreshUrl, true);
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            if (xhr.status >= 200 && xhr.status < 400) {
              // Success!
              var data = xhr.responseText;
              try {
                data = JSON.parse(data);
              } catch (e) {
                return;
              }

              if (data.preview) {
                document.getElementById('js-checkout-summary').parentNode.innerHTML = data.preview;
              }
            }
          }
        };

        xhr.send();
        pendingXhrs.push(xhr);
      } catch (e) {
        return;
      }
    }

    function initMyParcelCheckFrame() {
      if (typeof window.prestashop === 'undefined' || typeof window.prestashop.on !== 'function') {
        setTimeout(initMyParcelCheckFrame, 100);

        return;
      }

      window.prestashop.on('updatedDeliveryForm', refreshIframe);
      window.prestashop.on('changedCheckoutStep', refreshIframe);

      documentReady(function () {
        window.addEventListener('message', function (event) {
          if (!event.data) {
            return;
          }

          try {
            var data = JSON.parse(event.data);
          } catch (e) {
          }

          if (data
            && data.messageOrigin === 'myparcelcheckout'
            && data.subject === 'height'
          ) {
            var checkoutFrame = document.getElementById('myparcelcheckoutframe');
            if (data.height <= 0) {
              checkoutFrame.style.display = 'none';
            } else {
              checkoutFrame.style.display = 'block';
              checkoutFrame.height = parseInt(data.height, 10);
            }
          }
        });

        window.addEventListener('message', function (event) {
          if (!event.data) {
            return;
          }

          var data = JSON.parse(event.data);
          if (data
            && data.messageOrigin === 'myparcelcheckout'
            && data.subject === 'selection_changed'
          ) {
            if (pendingXhrs.length) {
              pendingXhrs.forEach(function (xhr) {
                xhr.abort();
              });
              pendingXhrs = [];
            }

            var selection = data.selection;

            /*
             *  If the `dontNotify` flag is set, first check if the server still needs the first notification
             *  for the selected delivery option
             */
            if (data.dontNotify) {
              if (window.mypaNotified && typeof window.mypaNotified === 'boolean') {
                return;
              }

              var idCarrier = getIdCarrier();
              if (idCarrier && window.mypaNotified === idCarrier) {
                return;
              }
            }

            var xhr = new XMLHttpRequest();
            xhr.open(
              'POST',
              '{$link->getModuleLink('myparcel', 'deliveryoptions', array(), Tools::usingSecureMode())|escape:'javascript':'UTF-8'}',
              true
            );

            xhr.onreadystatechange = function () {
              if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 400) {
                  // Success!
                  var data = xhr.responseText;
                  try {
                    data = JSON.parse(data);
                  } catch (e) {
                    return;
                  }

                  if (data.carrier_data) {
                    window.mypaNotified = getIdCarrier() || true;

                    for (var key in data.carrier_data) {
                      try {
                        var carrier = data.carrier_data[key];
                        document.querySelector('label[for=delivery_option_' + carrier.id +'] span.carrier-price').innerHTML = carrier.price;
                      } catch (e) {
                      }
                    }

                    refreshSummary();
                  }
                }
              }
            };

            xhr.send(JSON.stringify({
              ajax: true,
              updateOption: true,
              deliveryOption: selection,
            }));
            pendingXhrs.push(xhr);
          }
        });
      });
    }

    initMyParcelCheckFrame();
  }());
</script>
