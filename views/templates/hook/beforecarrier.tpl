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

    if (typeof document.createElement('_').classList === 'undefined') {
      Object.defineProperty(Element.prototype, 'classList', {
        get: function() {
          var self = this, bValue = self.className.split(" ")

          bValue.add = function (){
            var b;
            for(i in arguments){
              b = true;
              for (var j = 0; j<bValue.length;j++)
                if (bValue[j] == arguments[i]){
                  b = false
                  break
                }
              if(b)
                self.className += (self.className?" ":"")+arguments[i]
            }
          };
          bValue.remove = function(){
            self.className = ""
            for(i in arguments)
              for (var j = 0; j<bValue.length;j++)
                if(bValue[j] != arguments[i])
                  self.className += (self.className?" " :"")+bValue[j]
          };
          bValue.toggle = function(x){
            var b;
            if(x){
              self.className = ""
              b = false;
              for (var j = 0; j<bValue.length;j++)
                if(bValue[j] != x){
                  self.className += (self.className?" " :"")+bValue[j]
                  b = false
                } else b = true
              if(!b)
                self.className += (self.className?" ":"")+x
            } else throw new TypeError("Failed to execute 'toggle': 1 argument required")
            return !b;
          };

          return bValue;
        },
        enumerable: false
      });
    }

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
        var idAddressSelected = document.getElementById('id_address_delivery').value;

        return parseInt(document.querySelector('input[name="delivery_option[' + idAddressSelected + ']"][checked="checked]').value.trim(',.'), 10);
      } catch (e) {
        return 0;
      }
    }

    documentReady(function () {
      var xhr = null;

      window.addEventListener('message', function (event) {
        if (!event.data) {
          return;
        }

        try {
          var data = JSON.parse(event.data);
        } catch (e) {
          return;
        }

        if (data
          && data.messageOrigin === 'myparcelcheckout'
          && data.subject === 'height'
        ) {
          document.getElementById('myparcelcheckoutframe').height = parseInt(data.height, 10);
        }
      });

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
          && data.subject === 'selection_changed'
        ) {
          if (xhr) {
            xhr.abort();
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

          xhr = new XMLHttpRequest();
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

                  var carrierHTML = document.createElement('div');
                  carrierHTML.innerHTML = data.carrier_data.carrier_block;
                  carrierHTML = carrierHTML.querySelector('.delivery_options');

                  // Prevent the page from refreshing when the cart rules haven't been initialized on time
                  var removeMe = document.createElement('div');
                  removeMe.id = 'myparcel-remove-me';
                  removeMe.classList.add('cart_discount');

                  // Grab the delivery options HTML and inject
                  carrierHTML.appendChild(removeMe);
                  var deliveryOptions = document.querySelector('.delivery_options');
                  deliveryOptions.innerHTML = carrierHTML.innerHTML;
                  if (typeof window.updateCartSummary === 'function') {
                    window.updateCartSummary(data.summary);
                  }
                  if (typeof window.bindUniform === 'function') {
                    window.bindUniform();
                  }
                  if (typeof window.bindInputs === 'function') {
                    window.bindInputs();
                  }
                  removeMe.parentNode.removeChild(removeMe);
                }
              } else {
                xhr = null;
              }
            }
          };

          xhr.send(JSON.stringify({
            ajax: true,
            updateOption: true,
            deliveryOption: selection,
          }));
        }
      });
    });
  }());
</script>
