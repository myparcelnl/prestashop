import { EventBus } from '@/data/eventBus/EventBus';
import { OrderAction } from '@/data/global/actions';
import { ShipmentOptionsContext } from '@/data/global/context';
import { getOrderControllerUrl } from '@/data/eventBus/GetOrderControllerUrl';

type Response = SuccessResponse<{ context: ShipmentOptionsContext }>;

class ShipmentOptionsContextEventBus extends EventBus {
  public async refresh(orderId: number): Promise<Response> {
    const response = await this.get(getOrderControllerUrl(), {
      action: OrderAction.GET_SHIPMENT_OPTIONS_CONTEXT,
      orderId,
    });

    return response as Response;
  }
}

export const shipmentOptionsContextEventBus = new ShipmentOptionsContextEventBus();
