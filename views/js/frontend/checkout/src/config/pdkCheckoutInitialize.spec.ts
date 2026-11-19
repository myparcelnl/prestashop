/** @vitest-environment happy-dom */
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {mockLayout} from '../utils/__tests__/mockLayout';
import {pdkCheckoutInitialize} from './pdkCheckoutInitialize';

const HIDDEN_FORM = '<section id="step" hidden><form id="js-delivery"></form></section>';

const track = (promise: Promise<void>) => {
  const state = {resolved: false};
  void promise.then(() => {
    state.resolved = true;
  });
  return state;
};

describe('pdkCheckoutInitialize', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    mockLayout();
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
    document.body.innerHTML = '';
  });

  it('resolves at once when the delivery form is visible', async () => {
    document.body.innerHTML = '<section><form id="js-delivery"></form></section>';
    const state = track(pdkCheckoutInitialize());
    await vi.advanceTimersByTimeAsync(0);
    expect(state.resolved).toBe(true);
  });

  it('waits while the delivery form is not in the page yet', async () => {
    const state = track(pdkCheckoutInitialize());
    await vi.advanceTimersByTimeAsync(60_000);
    expect(state.resolved).toBe(false);
  });

  it('does not resolve while the delivery form stays hidden', async () => {
    document.body.innerHTML = HIDDEN_FORM;
    const state = track(pdkCheckoutInitialize());
    await vi.advanceTimersByTimeAsync(60_000);
    expect(state.resolved).toBe(false);
  });

  it('resolves within one interval after the delivery form becomes visible', async () => {
    document.body.innerHTML = HIDDEN_FORM;
    const state = track(pdkCheckoutInitialize());
    await vi.advanceTimersByTimeAsync(1_000);
    document.querySelector('#step')?.removeAttribute('hidden');
    await vi.advanceTimersByTimeAsync(250);
    expect(state.resolved).toBe(true);
  });

  it('resolves when the theme replaces the delivery form with a visible one', async () => {
    document.body.innerHTML = HIDDEN_FORM;
    const state = track(pdkCheckoutInitialize());
    document.body.innerHTML = '<section><form id="js-delivery"></form></section>';
    await vi.advanceTimersByTimeAsync(250);
    expect(state.resolved).toBe(true);
  });

  it('stops polling after it resolves', async () => {
    document.body.innerHTML = HIDDEN_FORM;
    void pdkCheckoutInitialize();
    document.querySelector('#step')?.removeAttribute('hidden');
    await vi.advanceTimersByTimeAsync(250);
    expect(vi.getTimerCount()).toBe(0);
  });
});
