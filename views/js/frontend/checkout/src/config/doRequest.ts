import {type FrontendEndpoint, type FrontendPdkEndpointObject} from '@myparcel-pdk/checkout-common';
import {type FrontendEndpointResponse} from '@myparcel-pdk/checkout';

export const doRequest = async <E extends FrontendEndpoint>(
  endpoint: FrontendPdkEndpointObject[E] & {baseUrl: string},
): Promise<FrontendEndpointResponse<E>> => {
  const query = new URLSearchParams(endpoint.parameters).toString();

  const response = await window.fetch(`${endpoint.baseUrl}/${endpoint.path}?${query}`, {
    method: endpoint.method,
    body: endpoint.body,
  });

  if (response.ok) {
    return response as FrontendEndpointResponse<E>;
  }

  throw new Error('Request failed');
};
