export function psOnChangedCheckoutStep(params: PsCallbackParameters): void {
  const {currentTarget} = params.event;

  if (currentTarget === null) {
    return;
  }

  const $currentTarget = $(currentTarget);

  if (!$currentTarget.hasClass('-current')) {
    const $activeStep = $('.checkout-step.-current');

    if (!$activeStep.length) {
      $currentTarget.addClass('-current');
      $currentTarget.addClass('js-current-step');
    }
  }
}
