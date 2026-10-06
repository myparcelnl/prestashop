/** @vitest-environment happy-dom */
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {tests, updateCheckoutForm} from '@myparcel-dev/pdk-checkout-common';
import {
  initializeCheckoutDeliveryOptions,
  useCheckoutStore,
  useDeliveryOptionsStore,
  usePdkCheckout,
} from '@myparcel-dev/pdk-checkout';
import {UPDATE_CONFIG_IN} from '@myparcel-dev/delivery-options';
import {updateDeliveryOptions} from '../deliveryOptions/updateDeliveryOptions';
import {formChange} from './formChange';
import {doRequest} from './doRequest';

const page = vi.hoisted(() => ({carrier: 'standard'}));

// The carrier on the page is a fixture; the PDK stores, adapter and events are real.
vi.mock('./getFormData', () => ({getFormData: () => ({shippingMethod: page.carrier})}));

vi.mock('../utils/useShippingMethodData', () => ({
  useShippingMethodData: () => ({
    shippingMethods: [
      {value: 'standard', carrier: 'dpd'},
      {value: 'flat_rate:1', carrier: 'postnl'},
    ],
  }),
}));

const makeContext = (value: number | null) => {
  const context = tests.getMockCheckoutContext();
  context.config = {
    ...context.config,
    physicalProperties: value === null ? null : {weight: value},
    carrierSettings: {dpd: {pricePickup: 2}, postnl: {pricePickup: 5}},
  };
  context.settings.actions.baseUrl = '/module/myparcelnl';
  Object.assign(context.settings.actions.endpoints, {
    proxyCapabilities: {parameters: {action: 'capabilities'}},
  });
  return context;
};

describe('PrestaShop checkout context integration', () => {
  let handlers: Record<string, () => void>;
  let context: ReturnType<typeof makeContext>;
  const updates: CustomEvent[] = [];
  const receive = (event: Event) => {
    updates.push(event as CustomEvent);
  };

  beforeEach(async () => {
    document.body.innerHTML = '';
    handlers = {};
    page.carrier = 'standard';
    context = makeContext(30000);
    tests.getMockCheckoutContext.mockReturnValueOnce(context);
    tests.doRequestSpy.mockImplementation(doRequest);
    vi.spyOn(window, 'fetch').mockImplementation(() =>
      Promise.resolve(new Response(JSON.stringify({data: {context: [{checkout: context}]}}))),
    );
    Object.assign(window, {
      prestashop: {
        on: (event: string, callback: () => void) => {
          handlers[event] = callback;
        },
      },
    });
    await tests.mockPdkCheckout();
    const element = document.querySelector('#delivery-options');

    if (!element) throw new Error('Expected the checkout widget container');

    element.innerHTML = '<div id="js-delivery">Rendered widget</div>';
    usePdkCheckout().onInitialize(() => initializeCheckoutDeliveryOptions({updateDeliveryOptions}));
    formChange(updateCheckoutForm);
    await vi.waitFor(() => expect(useDeliveryOptionsStore().state.enabled).toBe(true));
    updates.length = 0;
    document.addEventListener(UPDATE_CONFIG_IN, receive);
  });

  afterEach(() => {
    document.removeEventListener(UPDATE_CONFIG_IN, receive);
    vi.restoreAllMocks();
  });

  it('refreshes the weight after a carrier change and keeps only the selected carrier', async () => {
    context = makeContext(15000);
    page.carrier = 'flat_rate:1';
    tests.getFormDataSpy.mockReturnValue({...tests.getFormDataSpy(), 'shipping-method': 'flat_rate:1'});
    handlers.updatedDeliveryForm();

    // The carrier settings and the weight reach the store in separate updates.
    await vi.waitFor(() => {
      const {config} = useDeliveryOptionsStore().state.configuration;
      expect(config.carrierSettings).toEqual({postnl: {pricePickup: 5}});
      expect(config.physicalProperties).toEqual({weight: 15000});
      expect(updates.at(-1)?.detail.config.physicalProperties).toEqual({weight: 15000});
    });
  });

  it('does not refresh the context when another delivery form input changes', async () => {
    const fetch = vi.mocked(window.fetch);
    const requests = fetch.mock.calls.length;
    context = makeContext(15000);
    handlers.updatedDeliveryForm();

    await new Promise((resolve) => {
      setTimeout(resolve, 150);
    });
    expect(fetch.mock.calls.length).toBe(requests);
    expect(Object.keys(useDeliveryOptionsStore().state.configuration.config.carrierSettings ?? {})).toEqual(['dpd']);
  });
});
