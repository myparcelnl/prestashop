import { createMessagesCallback } from '@/composables/createMessagesCallback';
import { ref } from '@vue/composition-api';

describe('create messages callback', () => {
  const alerts = ref([]);
  const callback = createMessagesCallback(alerts, 'danger', 'body');

  it('creates a callback function', () => {
    expect(typeof callback).toBe('function');
  });

  it('updates alerts with the correct value', () => {
    callback({ response: { errors: [{ message: 'broken' }] }, parameters: {}, requestOptions: {}, url: '' });
    expect(alerts.value).toStrictEqual([{ content: 'broken', variant: 'danger' }]);
  });
});
