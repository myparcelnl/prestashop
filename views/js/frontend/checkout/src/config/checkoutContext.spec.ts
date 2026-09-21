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

// Platform carrier data is a fixture; the selected method, PDK stores, adapter and events are real.
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
    physicalProperties: value === null ? null : {weight: {value, unit: 'g'}},
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

  it('refreshes weight through fetch, JSON parsing, real stores and the widget event while retaining the selected carrier', async () => {
    for (const value of [15000, null]) {
      context = makeContext(value);
      handlers.updatedCart();
      await vi.waitFor(() => {
        expect(updates.at(-1)?.detail.config.physicalProperties).toEqual(context.config.physicalProperties);
      });
      expect(Object.keys(useDeliveryOptionsStore().state.configuration.config.carrierSettings ?? {})).toEqual(['dpd']);
      expect(useCheckoutStore().state.context.settings.actions.baseUrl).toBe('/module/myparcelnl');
    }

    tests.getFormDataSpy.mockReturnValueOnce({
      ...tests.getFormDataSpy(),
      'shipping-method': 'flat_rate:1',
    });
    handlers.updatedDeliveryForm();
    await vi.waitFor(() => {
      expect(useDeliveryOptionsStore().state.configuration.config.carrierSettings).toEqual({postnl: {pricePickup: 5}});
    });
  });

  it('keeps settings usable after a malformed response and recovers on the next cart change', async () => {
    const {settings} = useCheckoutStore().state.context;
    const warning = vi.spyOn(console, 'warn').mockImplementation(() => undefined);
    vi.spyOn(window, 'fetch').mockResolvedValueOnce(new Response(JSON.stringify({data: {context: []}})));
    handlers.updatedCart();

    await vi.waitFor(() => expect(warning).toHaveBeenCalled());
    expect(useCheckoutStore().state.context.settings).toBe(settings);
    expect(useDeliveryOptionsStore().state.configuration.config.physicalProperties).toBeNull();

    context = makeContext(15000);
    handlers.updatedCart();
    await vi.waitFor(() =>
      expect(useDeliveryOptionsStore().state.configuration.config.physicalProperties).toEqual(
        context.config.physicalProperties,
      ),
    );
  });
});
