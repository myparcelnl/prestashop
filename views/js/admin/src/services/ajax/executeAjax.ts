import { isOfType } from '@/utils/type-guard/isOfType';

interface AjaxResponse {
  data: SuccessResponse | ErrorResponse;
}

/**
 * Do an ajax request and format the response properly.
 */
export async function executeAjax(options: JQuery.AjaxSettings): Promise<AjaxResponse['data']> {
  let response = {} as AjaxResponse;

  try {
    response = await jQuery.ajax(options);
  } catch (error) {
    if (isOfType<JQuery.jqXHR>(error, 'responseJSON')) {
      return error.responseJSON;
    }

    if (isOfType<JQuery.jqXHR>(error, 'responseText')) {
      return { errors: [{ message: 'A server error has occurred.' }] };
    }
  }

  if (!isOfType<AjaxResponse>(response, 'data')) {
    throw new Error('Response could not be parsed.');
  }

  return response.data;
}
