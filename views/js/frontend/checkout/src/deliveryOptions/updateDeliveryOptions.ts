import {type DeliveryOptionsStoreState, useSettings} from '@myparcel-dev/pdk-checkout';
import {type FrontendEndpoint} from '@myparcel-dev/pdk-common';
import {getCurrentShippingMethod} from '../utils';
import {getDefaultDeliveryOptionsConfig} from './utils';

const getProxyCapabilitiesUrl = (): string | undefined => {
  const settings = useSettings();
  const endpoint = settings.actions.endpoints['proxyCapabilities' as FrontendEndpoint];

  if (!endpoint?.parameters || Object.keys(endpoint.parameters).length === 0) {
    console.warn('[myparcelnl] Missing or empty proxyCapabilities endpoint in checkout context');
    return undefined;
  }

  const query = new URLSearchParams(endpoint.parameters as Record<string, string>).toString();

  return `${settings.actions.baseUrl}?${query}`;
};

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export const updateDeliveryOptions = (state: DeliveryOptionsStoreState) => {
  const currentShippingMethod = getCurrentShippingMethod();
  const proxyCapabilities = getProxyCapabilitiesUrl();

  if (!currentShippingMethod) {
    return proxyCapabilities
      ? {...state.configuration.config, proxyCapabilities}
      : state.configuration.config;
  }

  const configuration = getDefaultDeliveryOptionsConfig();

  const {carrier} = currentShippingMethod;

  const config = {
    ...state.configuration.config,
    carrierSettings: {
      [carrier]: configuration.config?.carrierSettings?.[carrier] ?? {},
    },
  };

  return proxyCapabilities ? {...config, proxyCapabilities} : config;
};
