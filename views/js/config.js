/**
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
 */
function addLabelVar(text) {
  document.getElementById('MYPARCEL_LABEL_DESCRIPTION').value += text;
}

(function () {
  var clicks = 8;

  // Install .closest polyfill
  if (window.Element && !Element.prototype.closest) {
    Element.prototype.closest =
      function(s) {
        var matches = (this.document || this.ownerDocument).querySelectorAll(s),
          i,
          el = this;
        do {
          i = matches.length;
          while (--i >= 0 && matches.item(i) !== el) {};
        } while ((i < 0) && (el = el.parentElement));
        return el;
      };
  }

  function ready(fn) {
    if (document.readyState !== 'loading'){
      fn();
    } else if (document.addEventListener) {
      window.addEventListener('DOMContentLoaded', fn);
    } else {
      document.attachEvent('onreadystatechange', function() {
        if (document.readyState !== 'loading')
          fn();
      });
    }
  }

  function findGetParameter(parameterName) {
    var result = null,
      tmp = [];
    var items = location.search.substr(1).split('&');
    for (var index = 0; index < items.length; index++) {
      tmp = items[index].split('=');
      if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
  }

  function addEventListener(el, eventName, handler) {
    if (el.addEventListener) {
      el.addEventListener(eventName, handler);
    } else if (el.attachEvent) {
      el.attachEvent('on' + eventName, function() {
        handler.call(el);
      });
    } else {
      el[eventName] = function() {
        handler.call(el);
      };
    }
  }

  function checkAvailableMainSettings() {
    var automateCheckbox = document.querySelector('[name=MYPARCEL_UPDATE_OS]');
    var notificationsCheckbox = document.querySelector('[name=MYPARCEL_NOTIFS]');
    if (automateCheckbox == null || notificationsCheckbox == null) {
      return;
    }

    automate = automateCheckbox.checked;
    notifications = notificationsCheckbox.checked;

    var selectVisibility = {
      MYPARCEL_SHIPPED_STATUS: automate,
      MYPARCEL_RECEIVED_STATUS: automate,
      MYPARCEL_NOTIF_MOMENT: notifications,
    };

    Object.keys(selectVisibility).forEach(function (targetSelector) {
      try {
        var elem = document.querySelector('[name=' + targetSelector + ']');
        elem.disabled = !selectVisibility[targetSelector];
      } catch (e) {
      }
    });
  }

  function checkAvailableDeliverySettings() {
    if (document.querySelector('[name=mailbox_package]') == null) {
      return;
    }

    var mailboxPackage = document.querySelector('[name=mailbox_package]').checked;
    var digitalStamp = document.querySelector('[name=digital_stamp]').checked;
    var delivery = document.querySelector('[name=delivery]').checked;
    var pickup = document.querySelector('[name=pickup]').checked;
    var morning = document.querySelector('[name=morning]').checked;
    var evening = document.querySelector('[name=evening]').checked;
    var signed = document.querySelector('[name=signed]').checked;
    var recipientOnly = document.querySelector('[name=recipient_only]').checked;
    var morningPickup = document.querySelector('[name=morning_pickup]').checked;

    var formGroupVisibility = {
      mailbox_package: !digitalStamp && !pickup && !delivery,
      digital_stamp: !mailboxPackage && !pickup && !delivery,
      delivery: !digitalStamp && !mailboxPackage,
      pickup: !digitalStamp && !mailboxPackage,
      timeframe_days: !digitalStamp && !mailboxPackage && delivery,
      dropoff_delay: !digitalStamp && !mailboxPackage && delivery,
      morning: !digitalStamp && !mailboxPackage && delivery,
      morning_fee_tax_incl: !digitalStamp && !mailboxPackage && delivery && morning,
      evening: !digitalStamp && !mailboxPackage && delivery,
      evening_fee_tax_incl: !digitalStamp && !mailboxPackage && delivery && evening,
      signed: !digitalStamp && !mailboxPackage && delivery,
      signed_fee_tax_incl: !digitalStamp && !mailboxPackage && delivery && signed,
      recipient_only: !digitalStamp && !mailboxPackage && delivery,
      recipient_only_fee_tax_incl: !digitalStamp && !mailboxPackage && delivery && recipientOnly,
      signed_recipient_only_fee_tax_incl: !digitalStamp && !mailboxPackage && delivery && signed && recipientOnly,
      morning_pickup: !digitalStamp && !mailboxPackage && pickup,
      morning_pickup_fee_tax_incl: !digitalStamp && !mailboxPackage && pickup && morningPickup,
    };

    var panelVisibility = {
      fieldset_1_1: !digitalStamp && !mailboxPackage && delivery,
    };

    Object.keys(formGroupVisibility).forEach(function (targetSelector) {
      try {
        var elem = document.querySelector('[name=' + targetSelector + ']');
        if (typeof $ !== 'undefined') {
          if (formGroupVisibility[targetSelector]) {
            $(elem).closest('.margin-form').fadeIn().prev().fadeIn();
            $(elem).closest('.form-group').fadeIn();
          } else {
            $(elem).closest('.margin-form').fadeOut().prev().fadeOut();
            $(elem).closest('.form-group').fadeOut();
          }
        } else {
          elem = elem.closest('.form-group');
          elem.style.display = formGroupVisibility[targetSelector] ? 'block' : 'none';
        }
      } catch (e) {
      }
    });

    Object.keys(panelVisibility).forEach(function (targetId) {
      try {
        var elem = document.getElementById(targetId);
        if (typeof $ !== 'undefined') {
          if (panelVisibility[targetId]) {
            $(elem).fadeIn();
          } else {
            $(elem).fadeOut();
          }
        } else {
          elem.style.display = panelVisibility[targetId] ? 'block' : 'none';
        }
      } catch (e) {
      }
    });
  }

  function checkOptionsAvailability() {
    switch (parseInt(findGetParameter('menu'), 10)) {
      case 1:
        var fieldsets = ['fieldset_0', 'fieldset_1', 'fieldset_2', 'fieldset_3'];
        for (var key in fieldsets) {
          var fieldset = document.getElementById(fieldsets[key]);
          if (fieldset != null) {
            fieldset.style.display = 'block';
          }
        }
        break;
      case 2:
        if (parseInt(findGetParameter('id_myparcel_carrier_delivery_setting'), 10)) {
          checkAvailableDeliverySettings();
        }
        break;
      default:
        checkAvailableMainSettings();
        break;
    }
  }

  function devClick() {
    clicks--;
    if (clicks) {
      if (clicks > 0 && clicks < 5) {
        window.showSuccessMessage(clicks + ' clicks left before dev mode is enabled');
      }
    } else if (clicks === 0) {
      window.showSuccessMessage('dev mode enabled');
      [].slice.call(document.querySelectorAll('.myparcel-dev-hidden, .myparcel-dev-always-hidden')).forEach(function (elem) {
        elem.className = elem.className.replace('myparcel-dev-hidden', '').replace('myparcel-dev-always-hidden', '');
      });
    }
  }

  function devButton() {
    var button = document.getElementById('myparcel-dev-btn');
    if (button) {
      button.addEventListener('click', devClick);
    } else if (window._PS_VERSION_.substr(0, 3) === '1.5' && !Number(findGetParameter('menu'))) {
      const fieldset = document.querySelector('#fieldset_4');
      button = document.createElement('BUTTON');
      if (button == null || fieldset == null) {
        return;
      }
      button.className = 'button myparcel-dev-hidden';
      button.innerText = 'dev mode';
      button.type = 'button';
      button.onclick = devClick;
      fieldset.querySelector('div:nth-of-type(1)').appendChild(button);
    }
  }

  function addResetButton() {
    if (!parseInt(findGetParameter('menu'), 10)) {
      var html = document.createElement('BUTTON');
      html.classList.add('btn');
      html.classList.add('btn-default');
      html.innerHTML = '<i class="process-icon-refresh"></i> Reset';
      var fieldset = $('#fieldset_1_1 > .panel-footer');
      if (!fieldset.length) {
        fieldset = $('#fieldset_1 > .margin-form:last');
      }
      if (fieldset.length) {
        fieldset = fieldset[0];
        fieldset.appendChild(html);

        html.addEventListener('click', function (e) {
          e.preventDefault();
          try {
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_FG_COLOR1]').attr('id'), '#FFFFFF');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_FG_COLOR2]').attr('id'), '#000000');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_FG_COLOR3]').attr('id'), '#000000');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR1]').attr('id'), '#FBFBFB');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR2]').attr('id'), '#01BBC5');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR3]').attr('id'), '#75D3D8');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_HL_COLOR]').attr('id'), '#FF8C00');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_IA_COLOR]').attr('id'), '#848484');
            window.myparcelFontselect.checkout.setFont('Exo');
            $('[name=MYPARCEL_CHECKOUT_FSIZE]').val(2);
          } catch (e) {
          }
        });
      }
    }
  }

  ready(function () {
    addResetButton();
    devButton();
    checkOptionsAvailability();

    Array.prototype.slice.call(document.querySelectorAll('input')).forEach(function (item) {
      if (item) {
        addEventListener(item, 'change', checkOptionsAvailability);
      }
    });
  });
}());
