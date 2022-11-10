declare global {
  interface Window {
    MyParcelConfig: MyParcelDeliveryOptions.Configuration;
    // eslint-disable-next-line @typescript-eslint/naming-convention
    myparcel_delivery_options_url: string;
    prestashop: {
      on: (name: string, callback: (event: Event) => void) => void;
    };
  }
}

import {MyParcel, MyParcelDeliveryOptions} from '@myparcel/delivery-options';

(() => {
  /**
   * Whether the listeners have been initialized.
   */
  let initialized = false;

  /**
   * @type {{}}
   */
  const deliveryOptionsConfigStore: Partial<
    Record<
      MyParcel.CarrierID,
      {
        data: MyParcelDeliveryOptions.Configuration;
      }
    >
  > = {};

  function createOrMoveDeliveryOptionsForm($wrapper: JQuery): void {
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

  function getElement(selector: string): JQuery | null {
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

    const isOnDeliverStep =
      $checkoutDeliverStep.hasClass('js-current-step') || $checkoutDeliverStep.hasClass('-current');

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
  function getInput(): JQuery {
    let $input = $('#mypa-input');

    if (!$input.length) {
      $input = $('<input type="hidden" id="mypa-input" name="myparcel-delivery-options" />');

      const $wrapper = getDeliveryOptionsFormHandle();

      if ($wrapper) {
        $wrapper.append($input);
      }
    }

    return $input;
  }

  function getDeliveryOptionsFormHandle(): JQuery | null {
    return getElement('#myparcel-form-handle');
  }

  function hasUnRenderedDeliveryOptions(): boolean {
    return Boolean(getElement('#myparcel-delivery-options'));
  }

  /**
   * @param {string} carrierId
   */
  function updateConfig(carrierId: MyParcel.CarrierID) {
    const hasCarrierConfig = deliveryOptionsConfigStore.hasOwnProperty(carrierId);

    if (!hasCarrierConfig) {
      void $.ajax({
        url: `${window.myparcel_delivery_options_url}?carrier_id=${carrierId}`,
        dataType: 'json',
        async: false,
        success: function (data) {
          deliveryOptionsConfigStore[carrierId] = data;

          window.MyParcelConfig = deliveryOptionsConfigStore[carrierId]?.data ?? window.MyParcelConfig;
          updateDeliveryOptions();
        },
      });
    }

    window.MyParcelConfig = deliveryOptionsConfigStore[carrierId]?.data ?? window.MyParcelConfig;
  }

  /**
   * @param {jQuery} $deliveryOptionsRow
   */
  function initializeMyParcelForm($deliveryOptionsRow: JQuery) {
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
    if (hasUnRenderedDeliveryOptions()) {
      document.dispatchEvent(new Event('myparcel_render_delivery_options'));
    } else {
      document.dispatchEvent(new Event('myparcel_update_config'));
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

    window.prestashop.on('updatedDeliveryForm', (event) => {
      initializeMyParcelForm(event.deliveryOption);
    });

    initializeMyParcelForm(getDeliveryOptionsRow());

    document.addEventListener('myparcel_updated_delivery_options', (event) => {
      getDeliveryOptionsFormHandle().slideDown();

      if (event.detail) {
        updateInput(event.detail);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('#checkout-delivery-step.js-current-step')) {
      window.prestashop.on('changedCheckoutStep', start);
      return;
    }

    start();
  });

  // Hack to keep prestashop from hiding the checkout when icons inside the delivery options are clicked.
  window.prestashop.on('changedCheckoutStep', (values) => {
    const $currentTarget = $(values.event.currentTarget);

    if (!$currentTarget.hasClass('-current')) {
      const $activeStep = $('.checkout-step.-current');

      if (!$activeStep.length) {
        $currentTarget.addClass('-current');
        $currentTarget.addClass('js-current-step');
      }
    }
  });
})();
