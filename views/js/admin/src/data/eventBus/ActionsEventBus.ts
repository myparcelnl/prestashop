import { ActionResponse, AdminAction, ButtonAction } from '@/data/global/actions';
import { EventBus } from '@/data/eventBus/EventBus';

export type DoAction<T = AdminAction> = (action: T, parameters: RequestParameters) => Promise<ActionResponse<T>>;

export abstract class ActionsEventBus extends EventBus {
  protected abstract url: string;

  public async doAction<T = AdminAction>(action: AdminAction, parameters: RequestParameters = {}): Promise<ActionResponse<T>> {
    return await this.post(this.url, { action, ...parameters }) as ActionResponse<T>;
  }
}
