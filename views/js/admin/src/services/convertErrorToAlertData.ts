/**
 * Convert an ErrorResponse to an array of objects for use with the Alert component.
 */
export function convertErrorToAlertData(error: ErrorResponse): AlertData[] {
  return error.errors.map((error) => ({
    content: error.message.toString(),
    variant: 'danger',
  }));
}
