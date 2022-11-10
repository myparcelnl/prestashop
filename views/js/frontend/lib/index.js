"use strict";

// src/checkout.ts
(() => {
  let initialized = false;
  const deliveryOptionsConfigStore = {};
  function createOrMoveDeliveryOptionsForm($wrapper) {
    const $form = getDeliveryOptionsFormHandle();
    if ($form) {
      $form.hide();
      $wrapper.append($form);
    } else {
      $wrapper.html(`
        <div id="myparcel-form-handle">
          <div class="loader"></div>
          <div id="myparcel-delivery-options"></div>
        </div>
      `);
    }
  }
  function getElement(selector) {
    const element = $(selector);
    return element.length ? element : null;
  }
  function updateInput(data) {
    const $input = getInput();
    const dataString = JSON.stringify(data);
    $input.val(dataString);
    const $checkoutDeliverStep = $("#checkout-delivery-step");
    const isOnDeliverStep = $checkoutDeliverStep.hasClass("js-current-step") || $checkoutDeliverStep.hasClass("-current");
    if (isOnDeliverStep) {
      $input.trigger("change");
    }
  }
  function getDeliveryOptionsRow() {
    const row = $(".delivery-option input:checked").closest(".delivery-option");
    return row.length ? row : null;
  }
  function getInput() {
    let $input = $("#mypa-input");
    if (!$input.length) {
      $input = $('<input type="hidden" id="mypa-input" name="myparcel-delivery-options" />');
      const $wrapper = getDeliveryOptionsFormHandle();
      if ($wrapper) {
        $wrapper.append($input);
      }
    }
    return $input;
  }
  function getDeliveryOptionsFormHandle() {
    return getElement("#myparcel-form-handle");
  }
  function hasUnRenderedDeliveryOptions() {
    return Boolean(getElement("#myparcel-delivery-options"));
  }
  function updateConfig(carrierId) {
    var _a, _b;
    const hasCarrierConfig = deliveryOptionsConfigStore.hasOwnProperty(carrierId);
    if (!hasCarrierConfig) {
      void $.ajax({
        url: `${window.myparcel_delivery_options_url}?carrier_id=${carrierId}`,
        dataType: "json",
        async: false,
        success: function(data) {
          var _a2, _b2;
          deliveryOptionsConfigStore[carrierId] = data;
          window.MyParcelConfig = (_b2 = (_a2 = deliveryOptionsConfigStore[carrierId]) == null ? void 0 : _a2.data) != null ? _b2 : window.MyParcelConfig;
          updateDeliveryOptions();
        }
      });
    }
    window.MyParcelConfig = (_b = (_a = deliveryOptionsConfigStore[carrierId]) == null ? void 0 : _a.data) != null ? _b : window.MyParcelConfig;
  }
  function initializeMyParcelForm($deliveryOptionsRow) {
    if (!$deliveryOptionsRow || !$deliveryOptionsRow.length || !$deliveryOptionsRow.find("input:checked")) {
      return;
    }
    const carrierId = $deliveryOptionsRow.find("input:checked")[0].value.split(",").join("");
    const $wrapper = $deliveryOptionsRow.next().find(".myparcel-delivery-options-wrapper");
    if (!$wrapper) {
      return;
    }
    createOrMoveDeliveryOptionsForm($wrapper);
    updateConfig(carrierId);
    updateDeliveryOptions();
  }
  function updateDeliveryOptions() {
    if (hasUnRenderedDeliveryOptions()) {
      document.dispatchEvent(new Event("myparcel_render_delivery_options"));
    } else {
      document.dispatchEvent(new Event("myparcel_update_config"));
    }
  }
  function start() {
    if (initialized) {
      return;
    }
    initialized = true;
    window.prestashop.on("updatedDeliveryForm", (event) => {
      initializeMyParcelForm(event.deliveryOption);
    });
    initializeMyParcelForm(getDeliveryOptionsRow());
    document.addEventListener("myparcel_updated_delivery_options", (event) => {
      getDeliveryOptionsFormHandle().slideDown();
      if (event.detail) {
        updateInput(event.detail);
      }
    });
  }
  document.addEventListener("DOMContentLoaded", () => {
    if (!document.querySelector("#checkout-delivery-step.js-current-step")) {
      window.prestashop.on("changedCheckoutStep", start);
      return;
    }
    start();
  });
  window.prestashop.on("changedCheckoutStep", (values) => {
    const $currentTarget = $(values.event.currentTarget);
    if (!$currentTarget.hasClass("-current")) {
      const $activeStep = $(".checkout-step.-current");
      if (!$activeStep.length) {
        $currentTarget.addClass("-current");
        $currentTarget.addClass("js-current-step");
      }
    }
  });
})();
//# sourceMappingURL=index.js.map