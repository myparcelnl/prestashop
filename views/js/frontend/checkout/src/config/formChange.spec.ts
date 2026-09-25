import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {formChange} from './formChange';

const mocks = vi.hoisted(() => ({updateContext: vi.fn()}));

vi.mock('@myparcel-dev/pdk-checkout', () => ({
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

  it.each(['updatedDeliveryForm', 'updatedCart'])(
    'refreshes saved cart data after %s',
    async (event) => {
      events[event]();
      await vi.runAllTimersAsync();
      expect(callback).toHaveBeenCalledOnce();
      expect(mocks.updateContext).toHaveBeenCalledOnce();
      expect(callback.mock.invocationCallOrder[0]).toBeLessThan(
        mocks.updateContext.mock.invocationCallOrder[0],
      );
    },
  );

  it('coalesces delivery and cart events from the same update', async () => {
    events.updatedCart();
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(mocks.updateContext).toHaveBeenCalledOnce();
  });

  it('continues to update form values before the delivery options store exists', async () => {
    vi.stubGlobal('window', {MyParcelPdk: {stores: {}}});
    events.updatedDeliveryForm();
    await vi.runAllTimersAsync();
    expect(callback).toHaveBeenCalledOnce();
    expect(mocks.updateContext).not.toHaveBeenCalled();
  });

  it('ignores cart events outside the checkout delivery form', async () => {
    vi.stubGlobal('document', {querySelector: () => null});
    events.updatedCart();
    await vi.runAllTimersAsync();
    expect(callback).not.toHaveBeenCalled();
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
