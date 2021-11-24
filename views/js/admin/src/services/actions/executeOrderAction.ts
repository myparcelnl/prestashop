import { ContextKey } from '@/data/global/context';
import { OrderAction } from '@/data/global/actions';
import { executeAdminAction } from '@/services/actions/executeAdminAction';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { toArray } from '@/utils/toArray';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

type ExecuteOrderAction = (action: OrderAction, orderIdOrIds?: number| number[]) => Promise<void | SuccessResponse>;

export const executeOrderAction: ExecuteOrderAction = async(action, orderIdOrIds?) => {
  let orderIds: number[] = [];

  if (orderIdOrIds) {
    orderIds = toArray<number>(orderIdOrIds);
  } else {
    const { orderId } = useGlobalContext(ContextKey.SHIPMENT_OPTIONS).value;

    if (orderId) {
      orderIds.push(orderId);
    }
  }

  return executeAdminAction(orderActionsEventBus, action, { orderIds });
};
