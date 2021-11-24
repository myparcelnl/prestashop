import { ActionsEventBus } from '@/data/eventBus/ActionsEventBus';
import { getOrderControllerUrl } from '@/data/eventBus/GetOrderControllerUrl';

export class OrderActionsEventBus extends ActionsEventBus {
  protected url = getOrderControllerUrl();
}

export const orderActionsEventBus = new OrderActionsEventBus();
