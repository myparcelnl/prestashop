import { EmitterRequestData, EventCallbackData, EventName } from '@/data/eventBus/EventBus';
import { isOfType } from '@/utils/type-guard/isOfType';

/**
 *
 */
export function getMessagesFromResponse<T extends EventName>(
  data: EmitterRequestData<EventCallbackData<T>>,
): ResponseMessage[] {
  let messages: ResponseMessage[] = [];

  if (isOfType<SuccessResponseWithMessages>(data.response, 'messages')) {
    messages = data.response.messages;
  }

  if (isOfType<ErrorResponse>(data.response, 'errors')) {
    messages = data.response.errors;
  }

  return messages;
}
