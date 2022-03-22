import { ContextKey } from '@/data/global/context';
import { EventBus } from '@/data/eventBus/EventBus';
import { MyParcelDeliveryOptions } from '@myparcel/delivery-options';
import { OrderAction } from '@/data/global/actions';
import { getContext } from '@/data/global/getContext';
import { getOrderControllerUrl } from '@/data/eventBus/GetOrderControllerUrl';

class DeliveryOptionsConfigEventBus extends EventBus {
  /**
   * Retrieve current order's delivery options from PrestaShop.
   */
  public async getConfiguration(
    carrier: string | null,
  ): Promise<SuccessResponse<MyParcelDeliveryOptions.Configuration>> {
    return await this.doRequest(window.MyParcelActions.deliveryOptionsUrl, {
      carrier,
      addressId: getContext(ContextKey.SHIPPING_ADDRESS).addressId,
    }) as SuccessResponse<MyParcelDeliveryOptions.Configuration>;
  }

  /**
   * Saves bus data as new delivery options for current order.
   */
  public async saveConfiguration(): Promise<RequestResponse> {
    return this.post(getOrderControllerUrl(), { action: OrderAction.SAVE_DELIVERY_OPTIONS });
  }
}

export const deliveryOptionsEventBus = new DeliveryOptionsConfigEventBus();
