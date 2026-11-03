/** @vitest-environment happy-dom */
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {type DeliveryOptionsStoreState} from '@myparcel-dev/pdk-checkout';
import {onDeliveryOptionsOutputChange} from './updateCheckoutOnUpdate';

const mocks = vi.hoisted(() => ({trigger: vi.fn()}));

vi.mock('@myparcel-dev/pdk-checkout', () => ({
  useDeliveryOptionsStore: () => ({state: {hiddenInput: document.createElement('input')}}),
  debounce: (callback: (...args: unknown[]) => void, delay = 100) => {
    let timer: ReturnType<typeof setTimeout>;
    return (...args: unknown[]) => {
      clearTimeout(timer);
      timer = setTimeout(() => callback(...args), delay);
    };
  },
}));

vi.mock('../../utils', async (importOriginal) => ({
  ...(await importOriginal<typeof import('../../utils')>()),
  getCurrentShippingMethod: () => ({carrier: 'postnl'}),
}));

const state = (deliveryType: string) => ({output: {deliveryType}}) as unknown as DeliveryOptionsStoreState;

const setCurrentStep = (step: 'delivery' | 'payment') => {
  document.body.innerHTML = `
    <section id="checkout-delivery-step" class="${step === 'delivery' ? 'js-current-step' : ''}"></section>
    <section id="checkout-payment-step" class="${step === 'payment' ? 'js-current-step' : ''}"></section>
  `;
};

describe('onDeliveryOptionsOutputChange', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    mocks.trigger.mockReset();
    vi.stubGlobal('$', () => ({trigger: mocks.trigger}));
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
    document.body.innerHTML = '';
  });

  it('updates the delivery form on the delivery step', async () => {
    setCurrentStep('delivery');
    onDeliveryOptionsOutputChange(state('morning'), state('standard'));
    await vi.runAllTimersAsync();
    expect(mocks.trigger).toHaveBeenCalledWith('change');
  });

  it('does not update the delivery form when the output did not change', async () => {
    setCurrentStep('delivery');
    onDeliveryOptionsOutputChange(state('standard'), state('standard'));
    await vi.runAllTimersAsync();
    expect(mocks.trigger).not.toHaveBeenCalled();
  });

  it('does not update the delivery form on the payment step', async () => {
    setCurrentStep('payment');
    onDeliveryOptionsOutputChange(state('morning'), state('standard'));
    await vi.runAllTimersAsync();
    expect(mocks.trigger).not.toHaveBeenCalled();
  });

  it('checks the step when the debounce ends, not when the output changes', async () => {
    setCurrentStep('delivery');
    onDeliveryOptionsOutputChange(state('morning'), state('standard'));
    setCurrentStep('payment');
    await vi.runAllTimersAsync();
    expect(mocks.trigger).not.toHaveBeenCalled();
  });

  it('updates the delivery form when the theme has no delivery step', async () => {
    onDeliveryOptionsOutputChange(state('morning'), state('standard'));
    await vi.runAllTimersAsync();
    expect(mocks.trigger).toHaveBeenCalledWith('change');
  });
});
