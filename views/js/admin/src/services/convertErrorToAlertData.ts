import { EmitterRequestData } from '@/data/eventBus/EventBus';

/**
 * Convert an ErrorResponse to an array of objects for use with the Alert component.
 */
export function convertErrorToAlertData(data: EmitterRequestData<ErrorResponse>): AlertData[] {
  return data.response.errors.map((error) => ({
    content: error.message.toString(),
    variant: 'danger',
  }));
}
