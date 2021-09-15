!function(m) {
  /**
   * @param {number} i
   * @returns {?}
   */
  function t(i) {
    if (n[i]) {
      return n[i].exports;
    }

    const module = n[i] = {
      i: i,
      l: false,
      exports: {},
    };
    return m[i].call(module.exports, module, module.exports, t), module.l = true, module.exports;
  }

  var n = {};

  /** @type {!Array} */
  t.m = m;
  t.c = n;

  /**
   * @param {!Function} d
   * @param {string} name
   * @param {!Function} n
   * @returns {undefined}
   */
  t.d = function(d, name, n) {
    if (!t.o(d, name)) {
      Object.defineProperty(d, name, {
        enumerable: true,
        get: n,
      });
    }
  };

  /**
   * @param {!Object} x
   * @returns {undefined}
   */
  t.r = function(x) {
    if (typeof Symbol != 'undefined' && Symbol.toStringTag) {
      Object.defineProperty(x, Symbol.toStringTag, {
        value: 'Module',
      });
    }
    Object.defineProperty(x, '__esModule', {
      value: true,
    });
  };

  /**
   * @param {number} val
   * @param {number} byteOffset
   * @returns {?}
   */
  t.t = function(val, byteOffset) {
    if (1 & byteOffset && (val = t(val)), 8 & byteOffset) {
      return val;
    }
    if (4 & byteOffset && typeof val == 'object' && val && val.__esModule) {
      return val;
    }

    /** @type {!Object} */
    const d = Object.create(null);
    if (t.r(d), Object.defineProperty(d, 'default', {
      enumerable: true,
      value: val,
    }), 2 & byteOffset && typeof val != 'string') {
      let s;
      for (s in val) {
        t.d(d, s, function(attrPropertyName) {
          return val[attrPropertyName];
        }.bind(null, s));
      }
    }
    return d;
  };

  /**
   * @param {!Object} module
   * @returns {?}
   */
  t.n = function(module) {
    /** @type {function(): ?} */
    const n = module && module.__esModule
      ? function() {
        return module.default;
      }
      : function() {
        return module;
      };
    return t.d(n, 'a', n), n;
  };

  /**
   * @param {!Function} property
   * @param {string} object
   * @returns {?}
   */
  t.o = function(property, object) {
    return Object.prototype.hasOwnProperty.call(property, object);
  };

  /** @type {string} */
  t.p = '';
  t(t.s = 0);
}([
  function(canCreateDiscussions, isSlidingUp) {
    document.addEventListener('DOMContentLoaded', function(canCreateDiscussions) {
      /**
       * @param {!Object} name
       * @returns {undefined}
       */
      const t = function(name) {
        if (name && name.length && name.find('input:checked')) {
          const t = name.find('input:checked')[0].value.split(',').join('');
          const lnkDiv = name[0].nextElementSibling.querySelector('.myparcel-delivery-options-wrapper');
          if (lnkDiv) {
            $.ajax({
              url: `${myparcel_carrier_init_url}?id_carrier=${t}`,
              dataType: 'json',
              success: function(response) {
                window.MyParcelConfig = response;

                /** @type {(Element|null)} */
                const exMap = document.querySelector('.myparcel-delivery-options');
                if (exMap) {
                  exMap.remove();
                }

                /** @type {string} */
                lnkDiv.innerHTML = '<div id="myparcel-delivery-options"></div>';
                update(response.delivery_settings);
              },
            });
          }
        }
      };

      /**
       * @param {?} e
       * @returns {undefined}
       */
      var update = function(e) {
        const jField = $('.delivery-option input[type="radio"]:checked');
        let n = $('#mypa-input');
        if (!n.length) {
          n = $('<input type="hidden" class="mypa-post-nl-data" id="mypa-input" name="myparcel-delivery-options" />');
          const r = jField.closest('.delivery-option').next().find('.myparcel-delivery-options-wrapper');
          if (r.length) {
            r.append(n);
          }
        }

        /** @type {string} */
        const i = JSON.stringify(e);
        n.val(i);
        const khover = $('#checkout-delivery-step');
        if (khover.hasClass('js-current-step') || khover.hasClass('-current')) {
          n.trigger('change');
        }
        document.dispatchEvent(new Event('myparcel_render_delivery_options'));
      };
      if (typeof prestashop != 'undefined') {
        prestashop.on('updatedDeliveryForm', function(p) {
          t(p.deliveryOption);
        });
      }
      t($('.delivery-option input:checked').closest('.delivery-option'));
      document.addEventListener('myparcel_updated_delivery_options', function(event) {
        if (event.detail) {
          update(event.detail);
        }
      });
    });

    prestashop.on('changedCheckoutStep', function(gmu) {
      const {event} = gmu;
      const $tabHeading = $(event.currentTarget);
      if (!$tabHeading.hasClass('-current')) {
        if (!$('.checkout-step.-current').length) {
          $tabHeading.addClass('-current');
          $tabHeading.addClass('js-current-step');
        }
      }
    });
  },
]);
