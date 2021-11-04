document.addEventListener('DOMContentLoaded', function() {

  const packageTypeSelectList = document.getElementById('packageTypeSelect') || document.getElementById('packageType');

  if (packageTypeSelectList) {
    const findParentModal = function(element) {
      while ((element = element.parentElement)) {
        if (element.classList.contains('modal-content')) {
          return element;
        }
        if (element.classList.contains('card-body')) {
          return element;
        }
      }
      return document;
    };

    const toggleOptionsForPackageType = function() {
      const packageType = packageTypeSelectList.options[packageTypeSelectList.selectedIndex].value;
      const elements    = findParentModal(packageTypeSelectList).querySelectorAll('[data-for_package_type]');

      for (let i = 0, len = elements.length; i < len; ++i) {
        const forPackageTypes = elements[i].getAttribute('data-for_package_type').split(',');
        if (forPackageTypes.includes(packageType)) {
          elements[i].style.removeProperty('display');
        } else {
          elements[i].style.display = 'none';
        }
      }
    };
    packageTypeSelectList.addEventListener('change', toggleOptionsForPackageType);
    toggleOptionsForPackageType();
  }

  const insuranceCheckboxSelector = '.myparcel-insurance-checkbox';
  let toggleInsuranceAdditional = function() {
    const insuranceAdditionalActiveClassname = 'insurance-active';
    let $insuranceCheckbox = $(insuranceCheckboxSelector).first();
    let $insuranceAdditional = $('.insurance-additional-container');
    let isChecked = $insuranceCheckbox.is(':checked');

    if (isChecked) {
      $insuranceAdditional.addClass(insuranceAdditionalActiveClassname);
    } else {
      $insuranceAdditional.removeClass(insuranceAdditionalActiveClassname);
    }
  };
  $(document).on('change', insuranceCheckboxSelector, function() {
    toggleInsuranceAdditional();
  });
  toggleInsuranceAdditional();

  const insuranceHigherAmountSelector = '#myparcel-insurance-higher-amount';
  let changeInsuranceHigherAmount = function() {
    const amountStep = 500;
    let $insuranceHigherAmountInput = $(insuranceHigherAmountSelector);
    let currentValue = parseFloat($insuranceHigherAmountInput.val());

    let steps = Math.ceil(currentValue / amountStep);
    let newValue = amountStep * steps;
    if (newValue > 5000) {
      newValue = 5000;
    }
    $insuranceHigherAmountInput.val(newValue);
  };
  $(document).on('change', insuranceHigherAmountSelector, function() {
    changeInsuranceHigherAmount();
  });
  changeInsuranceHigherAmount();

  if ($('#MYPARCELBE_LABEL_SIZE').val() == 'a6') {
    $('.label_position').hide();
  }
  $('#MYPARCELBE_LABEL_SIZE').change(function() {
    if ($(this).val() == 'a6') {
      $('.label_position').hide();
    } else {
      $('.label_position').show();
    }
  });
  $(document).on('click', '.label-description-variables code', function() {
    var $input = $(this).closest('.col-lg-9').find('input[type="text"]');
    if ($input.length) {
      $input.val($input.val() + ' ' + $(this).html());
    }
  });

  $(document).on('change', '#psCarriers', function() {
    showCarrierName()
  });

  showCarrierName()

  function showCarrierName() {
    var psCarrier = $('#psCarriers').val();
    var carrierParent = $('#carrierName').parent().parent();
      
    if(parseInt(psCarrier) === 0) {
      $(carrierParent).removeClass('hidden')
    } else {
      $(carrierParent).addClass('hidden')
    }
  }

  if ($('body').hasClass('adminmodules') && $('#configuration_form').length) {
    $('.toggle-parent-field input[type="radio"]').on('change', function() {
      toggleFieldsVisibility($(this));
    });
    $('.toggle-parent-field input[type="radio"]:checked').each(function() {
      toggleFieldsVisibility($(this));
    });
  }
  function toggleFieldsVisibility($el) {
    var fieldName = $el.prop('name');
    if ($el.prop('value') === '1') {
      $('.toggle-child-field.' + fieldName).show();
    } else {
      $('.toggle-child-field.' + fieldName).hide();
    }
  }
}, false);
