import { EventCallback, EventName } from '@/data/eventBus/EventBus';
import { Ref } from '@vue/composition-api';
import { convertMessagesToAlertData } from '@/services/convertMessagesToAlertData';
import { getMessagesFromResponse } from '@/composables/getMessagesFromResponse';
import { scrollToElement } from '@/utils/scrollToElement';

/**
 *
 */
export function createMessagesCallback<EN extends EventName>(
  alerts: Ref<AlertData[]>,
  variant: Variant,
  errorElementSelector: string | undefined,
): EventCallback<EN> {
  return (data) => {
    const messages: ResponseMessage[] = getMessagesFromResponse(data);

    alerts.value = convertMessagesToAlertData(messages, variant);

    if (errorElementSelector) {
      scrollToElement(errorElementSelector);
    }
  };
}
