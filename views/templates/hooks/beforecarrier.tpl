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
<iframe name="myparcelcheckoutframe" id="myparcelcheckoutframe" src="{$myparcel_checkout_link|escape:'htmlall':'UTF-8' nofilter}" width="100%" height="600px" frameBorder="0"></iframe>
<div id="myparcel"></div>
<script type="text/javascript">
  (function () {
    function initMyParcelCheckFrame() {
      if (typeof $ === 'undefined') {
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
              success: function (data) {
                if (data && data.shouldRefresh && data.carrier_data) {
                  var $carrierHtml = $(data.carrier_data.carrier_block);
                  $carrierHtml = $carrierHtml.find('#myparcelcheckoutframe');
                  $('#myparcelcheckoutframe').html($carrierHtml[0]);

                  if (typeof window.updateCartSummary === 'function') {
                    window.updateCartSummary(data.summary);
                  }
                  if (typeof window.bindUniform === 'function') {
                    window.bindUniform();
                  }
                  if (typeof window.bindInputs === 'function') {
                    window.bindInputs();
                  }
                }
              },
              error: function () {
                xhr = null;
              }
            });
          }
        });
      });
    }

    initMyParcelCheckFrame();
  }());
</script>
