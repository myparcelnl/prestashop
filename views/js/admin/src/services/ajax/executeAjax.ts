import { isOfType } from '@/utils/type-guard/isOfType';

/**
 * Do an ajax request and format the response properly.
 */
export async function executeAjax(options: JQuery.AjaxSettings): Promise<AjaxResponse> {
  let response = {};

  try {
    response = await jQuery.ajax(options);
  } catch (error) {
    if (isOfType<JQuery.jqXHR>(error, 'responseJSON')) {
      return error.responseJSON;
    }

    if (isOfType<JQuery.jqXHR>(error, 'responseText')) {
      return { errors: ['A server error has occurred.'] };
    }
  }

  if (isOfType<AjaxErrorResponse>(response, 'errors')) {
    return response;
  }

  return response as AjaxSuccessResponse;
}
