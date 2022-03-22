import { OrderAction } from '@/data/global/actions';
import { executeOrderAction } from '@/services/actions/executeOrderAction';

/**
 * When a bulk action is clicked, gather the selected orders and execute related action.
 */
export function onClickBulkAction(action: OrderAction): void {
  const selectedOrderIds = $('#order_filter_form')
    .serializeArray()
    .filter((value) => value.name === 'order_orders_bulk[]')
    .map((item) => Number(item.value));

  void executeOrderAction(action, selectedOrderIds);
}
