import { EmitterRequestData, EventName } from '@/data/eventBus/EventBus';
import { OrderAction } from '@/data/global/actions';
import { convertErrorToAlertData } from '@/services/convertErrorToAlertData';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useNotices } from '@/composables/useNotices';

/**
 * When a bulk action is clicked, gather the selected orders and execute related action.
 */
export function onClickBulkAction(action: OrderAction): void {
  const selectedOrderIds = $('#order_filter_form')
    .serializeArray()
    .filter((value) => value.name === 'order_orders_bulk[]')
    .map((item) => Number(item.value));

  const { addNotice } = useNotices();

  const callback: (data: EmitterRequestData<ErrorResponse>) => void = (data): void => {
    convertErrorToAlertData(data).map(addNotice);
    orderActionsEventBus.off(EventName.ERROR, callback);
  };

  orderActionsEventBus.on(EventName.ERROR, callback);

  void executeOrderAction(action, selectedOrderIds);
}
