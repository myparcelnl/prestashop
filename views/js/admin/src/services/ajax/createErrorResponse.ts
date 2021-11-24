/**
 * Convert ajax response into a proper error object.
 */
export function createErrorResponse(data: AjaxErrorResponse): ErrorResponse {
  return {
    errors: data.errors?.map((message: string) => ({ message })) ?? [],
  };
}
