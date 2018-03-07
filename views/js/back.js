/**
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
 */
function addLabelVar(text) {
  document.getElementById('MYPARCEL_LABEL_DESCRIPTION').value += text;
}

(function () {
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

  //classList (IE9)
  /*! @license please refer to http://unlicense.org/ */
  /*! @author Eli Grey */
  /*! @source https://github.com/eligrey/classList.js */
  ;if("document" in self&&!("classList" in document.createElement("_"))){(function(j){"use strict";if(!("Element" in j)){return}var a="classList",f="prototype",m=j.Element[f],b=Object,k=String[f].trim||function(){return this.replace(/^\s+|\s+$/g,"")},c=Array[f].indexOf||function(q){var p=0,o=this.length;for(;p<o;p++){if(p in this&&this[p]===q){return p}}return -1},n=function(o,p){this.name=o;this.code=DOMException[o];this.message=p},g=function(p,o){if(o===""){throw new n("SYNTAX_ERR","An invalid or illegal string was specified")}if(/\s/.test(o)){throw new n("INVALID_CHARACTER_ERR","String contains an invalid character")}return c.call(p,o)},d=function(s){var r=k.call(s.getAttribute("class")||""),q=r?r.split(/\s+/):[],p=0,o=q.length;for(;p<o;p++){this.push(q[p])}this._updateClassName=function(){s.setAttribute("class",this.toString())}},e=d[f]=[],i=function(){return new d(this)};n[f]=Error[f];e.item=function(o){return this[o]||null};e.contains=function(o){o+="";return g(this,o)!==-1};e.add=function(){var s=arguments,r=0,p=s.length,q,o=false;do{q=s[r]+"";if(g(this,q)===-1){this.push(q);o=true}}while(++r<p);if(o){this._updateClassName()}};e.remove=function(){var t=arguments,s=0,p=t.length,r,o=false;do{r=t[s]+"";var q=g(this,r);if(q!==-1){this.splice(q,1);o=true}}while(++s<p);if(o){this._updateClassName()}};e.toggle=function(p,q){p+="";var o=this.contains(p),r=o?q!==true&&"remove":q!==false&&"add";if(r){this[r](p)}return !o};e.toString=function(){return this.join(" ")};if(b.defineProperty){var l={get:i,enumerable:true,configurable:true};try{b.defineProperty(m,a,l)}catch(h){if(h.number===-2146823252){l.enumerable=false;b.defineProperty(m,a,l)}}}else{if(b[f].__defineGetter__){m.__defineGetter__(a,i)}}}(self))};

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
    var delivery = document.querySelector('[name=delivery]').checked;
    var pickup = document.querySelector('[name=pickup]').checked;
    var morning = document.querySelector('[name=morning]').checked;
    var evening = document.querySelector('[name=evening]').checked;
    var signed = document.querySelector('[name=signed]').checked;
    var recipientOnly = document.querySelector('[name=recipient_only]').checked;
    var morningPickup = document.querySelector('[name=morning_pickup]').checked;

    var formGroupVisibility = {
      mailbox_package: true,
      delivery: !mailboxPackage,
      pickup: !mailboxPackage,
      timeframe_days: !mailboxPackage && delivery,
      dropoff_delay: !mailboxPackage && delivery,
      morning: !mailboxPackage && delivery,
      morning_fee_tax_incl: !mailboxPackage && delivery && morning,
      evening: !mailboxPackage && delivery,
      evening_fee_tax_incl: !mailboxPackage && delivery && evening,
      signed: !mailboxPackage && delivery,
      signed_fee_tax_incl: !mailboxPackage && delivery && signed,
      recipient_only: !mailboxPackage && delivery,
      recipient_only_fee_tax_incl: !mailboxPackage && delivery && recipientOnly,
      signed_recipient_only_fee_tax_incl: !mailboxPackage && delivery && signed && recipientOnly,
      morning_pickup: !mailboxPackage && pickup,
      morning_pickup_fee_tax_incl: !mailboxPackage && pickup && morningPickup,
    };

    var panelVisibility = {
      fieldset_1_1: !mailboxPackage && delivery,
    };

    Object.keys(formGroupVisibility).forEach(function (targetSelector) {
      try {
        var elem = document.querySelector('[name=' + targetSelector + ']');
        elem = elem.closest('.form-group');
        elem.style.display = formGroupVisibility[targetSelector] ? 'block' : 'none';
      } catch (e) {
      }
    });

    Object.keys(panelVisibility).forEach(function (targetId) {
      try {
        var elem = document.getElementById(targetId);
        elem.style.display = panelVisibility[targetId] ? 'block' : 'none';
      } catch (e) {
      }
    });
  }

  function checkOptionsAvailability() {
    switch (parseInt(findGetParameter('menu'), 10)) {
      case 1:
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

  function addResetButton() {
    if (!parseInt(findGetParameter('menu'), 10)) {
      var html = document.createElement('a');
      html.classList.add('btn');
      html.classList.add('btn-default');
      html.innerHTML = '<i class="process-icon-refresh"></i> Reset';
      var fieldset = $('#fieldset_1_1 > .panel-footer');
      if (fieldset.length) {
        fieldset = fieldset[0];
        fieldset.appendChild(html);


        html.addEventListener('click', function (e) {
          e.preventDefault();
          try {
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_FG_COLOR1]').attr('id'), '#FFFFFF');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_FG_COLOR2]').attr('id'), '#000000');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR1]').attr('id'), '#FBFBFB');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR2]').attr('id'), '#01BBC5');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_BG_COLOR3]').attr('id'), '#75D3D8');
            $.fn.mColorPicker.setInputColor($('[name=MYPARCEL_CHECKOUT_HL_COLOR]').attr('id'), '#FF8C00');
            window.stripeFontselect.checkout.setFont('Exo');
            $('[name=MYPARCEL_CHECKOUT_FSIZE]').val(2);
          } catch (e) {
          }
        });
      }
    }
  }

  ready(function () {
    addResetButton();
    checkOptionsAvailability();

    Array.prototype.slice.call(document.querySelectorAll('input')).forEach(function (item) {
      if (item) {
        addEventListener(item, 'change', checkOptionsAvailability);
      }
    });
  });
}());
