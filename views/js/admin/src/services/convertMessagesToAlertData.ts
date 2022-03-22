/**
 * Convert an ErrorResponse to an array of objects for use with the Alert component.
 */
export function convertMessagesToAlertData(messages: ResponseMessage[], variant: Variant): AlertData[] {
  return messages.map((message) => ({
    content: message.message,
    variant,
  }));
}
