import { OrderAction } from '@/data/global/actions';
import { onClickBulkAction } from '@/services/bulk-actions/onClickBulkAction';

/**
 * Attach a listener to each bulk action button in the order list.
 */
export function attachBulkActionHandlers(): void {
  Object
    .values(OrderAction)
    .forEach((action: OrderAction) => {
      $('#order_grid_bulk_action_' + action).on('click', () => onClickBulkAction(action));
    });
}
