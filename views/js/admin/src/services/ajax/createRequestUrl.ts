import { filterParameters } from '@/services/ajax/filterParameters';

/**
 * Create a full url from a string and a parameters object.
 */
export function createRequestUrl(url: string, parameters: RequestParameters): string {
  const params = jQuery.param(filterParameters(parameters));

  if (params) {
    const prefix = url.includes('?') ? '&' : '?';
    url += prefix + params;
  }

  return url;
}
