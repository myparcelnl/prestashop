import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {formChange} from './formChange';

const mocks = vi.hoisted(() => ({
  updateContext: vi.fn(),
  storedShippingMethod: 'standard',
  formShippingMethod: 'flat_rate:1',
}));

vi.mock('./getFormData', () => ({
  getFormData: () => ({shippingMethod: mocks.formShippingMethod}),
}));

vi.mock('@myparcel-dev/pdk-checkout', () => ({
  PdkField: {ShippingMethod: 'shippingMethod'},
  useCheckoutStore: () => ({state: {form: {shippingMethod: mocks.storedShippingMethod}}}),
  updateContext: mocks.updateContext,
  debounce: (callback: () => void, delay = 100) => {
    let timer: ReturnType<typeof setTimeout>;
    return () => {
      clearTimeout(timer);
      timer = setTimeout(callback, delay);
    };
  },
}));

describe('formChange', () => {
  let events: Record<string, () => void>;
  let callback: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.useFakeTimers();
    vi.clearAllMocks();
    events = {};
    callback = vi.fn();
    mocks.updateContext.mockResolvedValue(undefined);
    mocks.storedShippingMethod = 'standard';
    mocks.formShippingMethod = 'flat_rate:1';
    vi.stubGlobal('window', {
      prestashop: {
        on: (event: string, handler: () => void) => {
          events[event] = handler;
        },
      },
      MyParcelPdk: {stores: {deliveryOptions: {}}},
    });
    vi.stubGlobal('document', {querySelector: vi.fn(() => ({}))});
    formChange(callback);
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('refreshes the context before it updates the form after a carrier change', async () => {
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(callback).toHaveBeenCalledOnce();
    expect(mocks.updateContext).toHaveBeenCalledOnce();
    expect(mocks.updateContext.mock.invocationCallOrder[0]).toBeLessThan(
      callback.mock.invocationCallOrder[0],
    );
  });

  it('does not refresh the context when the carrier did not change', async () => {
    mocks.formShippingMethod = 'standard';
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(callback).toHaveBeenCalledOnce();
    expect(mocks.updateContext).not.toHaveBeenCalled();
  });

  it('continues to update form values before the delivery options store exists', async () => {
    vi.stubGlobal('window', {MyParcelPdk: {stores: {}}});
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(callback).toHaveBeenCalledOnce();
    expect(mocks.updateContext).not.toHaveBeenCalled();
  });

  it('handles a failed context request without an unhandled rejection', async () => {
    const error = new Error('Network unavailable');
    const warning = vi
      .spyOn(console, 'warn')
      .mockImplementation(() => undefined);
    mocks.updateContext.mockRejectedValueOnce(error);
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(callback).toHaveBeenCalledOnce();
    expect(warning).toHaveBeenCalledWith(
      '[myparcelnl] Could not refresh the checkout context',
      error,
    );
  });
});
