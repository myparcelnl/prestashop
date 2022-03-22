import { EventBus, EventName } from '@/data/eventBus/EventBus';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';

const eventData = { response: {}, parameters: {}, requestOptions: {}, url: '' };

class TestEventBus extends EventBus {
  public triggerBusy(): void {
    this.emit(EventName.BUSY, { ...eventData, response: true });
  }

  public triggerResponse(): void {
    this.emit(EventName.RESPONSE, { ...eventData, response: { messages: [{ message: 'yay!' }] } });
  }

  public triggerError(): void {
    this.emit(EventName.ERROR, { ...eventData, response: { errors: [{ message: 'nay!' }] } });
  }
}

describe('use eventbus alerts', () => {
  const testEventBus = new TestEventBus();
  const alerts = useEventBusAlerts(testEventBus);

  it('starts with an empty alerts array', () => {
    expect(alerts.alerts.value).toStrictEqual([]);
  });

  it('fills the alerts array with messages on response', () => {
    testEventBus.triggerResponse();

    expect(alerts.alerts.value).toStrictEqual([
      {
        content: 'yay!',
        variant: 'success',
      },
    ]);
  });

  it('empties alerts array after becoming busy again', () => {
    testEventBus.triggerBusy();
    expect(alerts.alerts.value).toStrictEqual([]);
  });

  it('fills the alerts array with errors on error', () => {
    testEventBus.triggerError();

    expect(alerts.alerts.value).toStrictEqual([
      {
        content: 'nay!',
        variant: 'danger',
      },
    ]);
  });
});
