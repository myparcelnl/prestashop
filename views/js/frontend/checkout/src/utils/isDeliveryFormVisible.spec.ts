/** @vitest-environment happy-dom */
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {isDeliveryFormVisible} from './isDeliveryFormVisible';
import {mockLayout} from './__tests__/mockLayout';

describe('isDeliveryFormVisible', () => {
  beforeEach(mockLayout);

  afterEach(() => {
    vi.restoreAllMocks();
    document.body.innerHTML = '';
  });

  it('returns true when the delivery form is visible', () => {
    document.body.innerHTML = '<section><form id="js-delivery"></form></section>';
    expect(isDeliveryFormVisible()).toBe(true);
  });

  it('returns false when the delivery form is hidden', () => {
    document.body.innerHTML = '<section hidden><form id="js-delivery"></form></section>';
    expect(isDeliveryFormVisible()).toBe(false);
  });

  it('returns false when the delivery form is not in the page', () => {
    expect(isDeliveryFormVisible()).toBe(false);
  });
});
