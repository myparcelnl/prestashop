(function () {
  function checkFormControls(checked) {
    if (checked) {
      $('.form-group').hide();
      $('input[name=mailbox_package]').first().closest('.form-group').show();

      // Refers to delivery panel
      $('i.icon-clock-o').closest('.panel').hide();
    } else {
      $('.form-group').show();

      // Refers to delivery panel
      $('i.icon-clock-o').closest('.panel').show();
    }
  }

  function initMyParcelBO() {
    if (typeof $ === 'undefined') {
      setTimeout(initMyParcelBO, 100);

      return;
    }

    $(document).ready(function () {
      if ($('input[name=mailbox_package]').length <= 0) {
        return;
      }

      $('input[name=mailbox_package]').change(function () {
        checkFormControls(!!this.value);
      });

      checkFormControls(!!$('input[name=mailbox_package]').attr('checked'));
    });
  }

  initMyParcelBO();
}());
