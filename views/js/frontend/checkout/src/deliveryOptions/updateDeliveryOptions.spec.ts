import {beforeEach, describe, expect, it, vi} from 'vitest';
import {updateDeliveryOptions} from './updateDeliveryOptions';
import {getDefaultDeliveryOptionsConfig} from './utils/getDefaultDeliveryOptionsConfig';

const mocks = vi.hoisted(() => ({
  context: {config: {} as Record<string, unknown>},
  deliveryOptions: {
    state: {configuration: {config: {} as Record<string, unknown>}},
  },
  currentMethod: vi.fn(),
}));

vi.mock('@myparcel-dev/pdk-checkout', () => ({
  useCheckoutStore: () => ({state: {context: mocks.context}}),
  useDeliveryOptionsStore: () => mocks.deliveryOptions,
  useSettings: () => ({
    actions: {
      baseUrl: '/module/myparcelnl',
      endpoints: {proxyCapabilities: {parameters: {action: 'capabilities'}}},
    },
  }),
}));
vi.mock('../utils', () => ({getCurrentShippingMethod: mocks.currentMethod}));

describe('updateDeliveryOptions', () => {
  beforeEach(() => {
    mocks.currentMethod.mockReturnValue({carrier: 'dpd'});
    mocks.context.config = {
      physicalProperties: {weight: 30000},
      carrierSettings: {dpd: {pricePickup: 1}, postnl: {pricePickup: 2}},
    };
    mocks.deliveryOptions.state.configuration.config = {
      ...mocks.context.config,
    };
  });

  it('retains only the selected carrier after a context refresh', () => {
    // Read the initial config before refreshing, as checkout initialization does.
    getDefaultDeliveryOptionsConfig();
    mocks.context.config = {
      physicalProperties: null,
      carrierSettings: {dpd: {pricePickup: 4}, postnl: {pricePickup: 5}},
    };
    mocks.deliveryOptions.state.configuration.config = {
      ...mocks.context.config,
    };
    const result = updateDeliveryOptions(
      mocks.deliveryOptions.state as Parameters<
        typeof updateDeliveryOptions
      >[0],
    );
    expect(result).toMatchObject({
      physicalProperties: null,
      carrierSettings: {dpd: {pricePickup: 4}},
    });
    expect(Object.keys(result.carrierSettings ?? {})).toEqual(['dpd']);
  });

  it('uses the fresh full carrier list when the selected shipping method changes', () => {
    const initial = updateDeliveryOptions(
      mocks.deliveryOptions.state as Parameters<
        typeof updateDeliveryOptions
      >[0],
    );
    mocks.deliveryOptions.state.configuration.config = initial;
    mocks.context.config = {
      ...mocks.context.config,
      carrierSettings: {dpd: {pricePickup: 4}, postnl: {pricePickup: 5}},
    };
    mocks.currentMethod.mockReturnValue({carrier: 'postnl'});
    const result = updateDeliveryOptions(
      mocks.deliveryOptions.state as Parameters<
        typeof updateDeliveryOptions
      >[0],
    );
    expect(result.carrierSettings).toEqual({postnl: {pricePickup: 5}});
  });
});
