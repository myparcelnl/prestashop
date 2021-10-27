/**
 * @member {string} window.myparcel_carrier_init_url
 * @member {jQuery} $
 */

(() => {
  /**
   * Whether the listeners have been initialized.
   *
   * @type {boolean}
   */
  let initialized = false;

  /**
   * @type {{}}
   */
  const deliveryOptionsConfigStore = {};

  /**
   * @param {jQuery} $wrapper
   */
  function createOrMoveDeliveryOptionsForm($wrapper) {
    const $form = getDeliveryOptionsFormHandle();

    if ($form) {
      // Move the form to the new delivery option
      $form.hide();
      $wrapper.append($form);
    } else {
      // Or create the form container if it doesn't exist yet
      $wrapper.html(`
        <div id="myparcel-form-handle">
          <div class="loader"></div>
          <div id="myparcel-delivery-options"></div>
        </div>
      `);
    }
  }

  /**
   * @param {string} selector
   *
   * @returns {jQuery|null}
   */
  function getElement(selector) {
    const element = $(selector);
    return element.length ? element : null;
  }

  /**
   * @param {Object} data
   */
  function updateInput(data) {
    const $input = getInput();
    const dataString = JSON.stringify(data);

    $input.val(dataString);

    const $checkoutDeliverStep = $('#checkout-delivery-step');

    const isOnDeliverStep = $checkoutDeliverStep.hasClass('js-current-step')
      || $checkoutDeliverStep.hasClass('-current');

    if (isOnDeliverStep) {
      $input.trigger('change');
    }
  }

  /**
   * @returns {jQuery|null}
   */
  function getDeliveryOptionsRow() {
    const row = $('.delivery-option input:checked').closest('.delivery-option');
    return row.length ? row : null;
  }

  /**
   * @returns {jQuery}
   */
  function getInput() {
    let $input = $('#mypa-input');

    if (!$input.length) {
      $input = $('<input type="hidden" id="mypa-input" name="myparcel-delivery-options" />');

      const $wrapper = getDeliveryOptionsFormHandle();

      if ($wrapper.length) {
        $wrapper.append($input);
      }
    }

    return $input;
  }

  /**
   * @returns {jQuery|null}
   */
  function getDeliveryOptionsFormHandle() {
    return getElement('#myparcel-form-handle');
  }

  /**
   * @returns {jQuery|null}
   */
  function getRenderedDeliveryOptions() {
    return getElement('form.myparcel-delivery-options');
  }

  /**
   * @param {string} carrierId
   */
  function updateConfig(carrierId) {
    const hasCarrierConfig = deliveryOptionsConfigStore.hasOwnProperty(carrierId);

    if (!hasCarrierConfig) {
      $.ajax({
        url: `${window.myparcel_carrier_init_url}?id_carrier=${carrierId}`,
        dataType: 'json',
        async: false,
        success: function(data) {
          deliveryOptionsConfigStore[carrierId] = data;
        },
      });
    }

    window.MyParcelConfig = deliveryOptionsConfigStore[carrierId];
  }

  /**
   * @param {jQuery} $deliveryOptionsRow
   */
  function initializeMyParcelForm($deliveryOptionsRow) {
    if (!$deliveryOptionsRow || !$deliveryOptionsRow.length || !$deliveryOptionsRow.find('input:checked')) {
      return;
    }

    const carrierId = $deliveryOptionsRow.find('input:checked')[0].value.split(',').join('');
    const $wrapper = $deliveryOptionsRow.next().find('.myparcel-delivery-options-wrapper');

    if (!$wrapper) {
      return;
    }

    createOrMoveDeliveryOptionsForm($wrapper);
    updateConfig(carrierId);
    updateDeliveryOptions();
  }

  /**
   *
   */
  function updateDeliveryOptions() {
    if (getRenderedDeliveryOptions()) {
      document.dispatchEvent(new Event('myparcel_update_config'));
    } else {
      document.dispatchEvent(new Event('myparcel_render_delivery_options'));
    }
  }

  /**
   * Initialize all listeners.
   */
  function start() {
    if (initialized) {
      return;
    }

    initialized = true;

    prestashop.on('updatedDeliveryForm', (event) => {
      initializeMyParcelForm(event.deliveryOption);
    });

    initializeMyParcelForm(getDeliveryOptionsRow());

    document.addEventListener(
      'myparcel_updated_delivery_options',
      (event) => {
        getDeliveryOptionsFormHandle().slideDown();
        if (event.detail) {
          updateInput(event.detail);
        }
      },
    );
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('#checkout-delivery-step.js-current-step')) {
      prestashop.on('changedCheckoutStep', start);
      return;
    }

    start();
  });
})();
