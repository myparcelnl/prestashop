import { ActionResponse, AdminAction } from '@/data/global/actions';
import { EventBus } from '@/data/eventBus/EventBus';

export type DoAction<T = AdminAction> = (action: T, parameters: RequestParameters) => Promise<ActionResponse<T>>;

export class ActionsEventBus extends EventBus {
  protected url: string | undefined;

  public doAction: DoAction = async(action, parameters = {}) => {
    if (!this.url) {
      throw new Error('Property "url" must be defined.');
    }

    return await this.post(this.url, { action, ...parameters }) as ActionResponse;
  };
}
