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
<iframe name="myparcelcheckoutframe" id="myparcelcheckoutframe" src="{$link->getModuleLink('myparcel', 'myparcelcheckout', array(), true)}" width="100%" height="600px" frameBorder="0"></iframe>
<div id="myparcel"></div>
<script type="text/javascript">
  $(document).ready(function () {
    var myparcelFrame = frames['myparcelcheckoutframe'];
    var xhr = null;

    window.addEventListener('message', function (event) {
      if (event.data
        && event.data.messageOrigin === 'myparcelcheckout'
        && event.data.subject === 'height'
      ) {
        $('#myparcelcheckoutframe').height(parseInt(event.data.height, 10));
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
          url: '{$link->getModuleLink('myparcel', 'deliveryoptions', array(), true)|escape:'javascript':'UTF-8'}',
          type: 'post',
          data: {
            ajax: true,
            updateOption: true,
            deliveryOption: selection,
          },
          success: function (data) {
            if (data.carrier_data) {
              var $carrierHtml = $(data.carrier_data.carrier_block);
              $carrierHtml = $carrierHtml.find('.delivery_options');
              $('.delivery_options').html($carrierHtml[0]);

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
</script>
