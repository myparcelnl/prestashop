import { createRequestUrl } from '@/services/ajax/createRequestUrl';
import { executeAjax } from '@/services/ajax/executeAjax';
import { isOfType } from '@/utils/type-guard/isOfType';

export type DoRequest<Response = SuccessResponse | ErrorResponse> = (
  url: string,
  parameters: RequestParameters,
  requestOptions?: JQuery.AjaxSettings | null,
) => Promise<Response>;

/**
 * Execute a request and prepare its response for use.
 */
export const doRequest: DoRequest = async(url, parameters, requestOptions) => {
  const response = await executeAjax({
    url: createRequestUrl(url, parameters),
    dataType: 'json',
    async: true,
    cache: false,
    ...requestOptions ?? {},
  });

  if (isOfType<ErrorResponse>(response, 'errors')) {
    return response;
  }

  return {
    ...response,
    action: parameters.action,
  };
};
