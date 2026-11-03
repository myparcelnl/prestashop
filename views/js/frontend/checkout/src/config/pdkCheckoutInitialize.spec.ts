/** @vitest-environment happy-dom */
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {pdkCheckoutInitialize} from './pdkCheckoutInitialize';

const STEPS = `
  <section id="checkout-delivery-step" class="checkout-step"></section>
  <section id="checkout-payment-step" class="checkout-step js-current-step"></section>
`;

const isResolved = async (promise: Promise<void>): Promise<boolean> => {
  let resolved = false;
  void promise.then(() => {
    resolved = true;
  });
  await vi.advanceTimersByTimeAsync(0);
  return resolved;
};

describe('pdkCheckoutInitialize', () => {
  let events: Record<string, () => void>;

  beforeEach(() => {
    vi.useFakeTimers();
    events = {};
    Object.assign(window, {
      prestashop: {
        on: (event: string, handler: () => void) => {
          events[event] = handler;
        },
      },
    });
  });

  afterEach(() => {
    vi.useRealTimers();
    document.body.innerHTML = '';
  });

  it('resolves at once when the delivery step is current', async () => {
    document.body.innerHTML = '<section id="checkout-delivery-step" class="js-current-step"></section>';
    expect(await isResolved(pdkCheckoutInitialize())).toBe(true);
  });

  it('does not resolve on the payment step, also not after the fallback delay', async () => {
    document.body.innerHTML = STEPS;
    const promise = pdkCheckoutInitialize();
    await vi.advanceTimersByTimeAsync(60_000);
    expect(await isResolved(promise)).toBe(false);
  });

  it('resolves when the customer goes back to the delivery step', async () => {
    document.body.innerHTML = STEPS;
    const promise = pdkCheckoutInitialize();
    document.querySelector('#checkout-delivery-step')?.classList.add('js-current-step');
    events.changedCheckoutStep?.();
    expect(await isResolved(promise)).toBe(true);
  });

  describe('when the theme inserts the delivery step later', () => {
    // happy-dom does not deliver MutationObserver records while fake timers are active.
    beforeEach(() => {
      vi.useRealTimers();
    });

    it('resolves when the inserted step is already current', async () => {
      const promise = pdkCheckoutInitialize();
      document.body.innerHTML = '<section id="checkout-delivery-step" class="js-current-step"></section>';
      await expect(promise).resolves.toBeUndefined();
    });

    it('resolves when the inserted step becomes current', async () => {
      let resolved = false;
      const promise = pdkCheckoutInitialize().then(() => {
        resolved = true;
      });
      document.body.innerHTML = STEPS;
      await new Promise((resolve) => {
        setTimeout(resolve, 10);
      });
      expect(resolved).toBe(false);
      document.querySelector('#checkout-delivery-step')?.classList.add('js-current-step');
      await expect(promise).resolves.toBeUndefined();
    });
  });

  it('resolves after the fallback delay when the theme has no delivery step', async () => {
    const promise = pdkCheckoutInitialize();
    expect(await isResolved(promise)).toBe(false);
    await vi.advanceTimersByTimeAsync(10_000);
    expect(await isResolved(promise)).toBe(true);
  });
});
