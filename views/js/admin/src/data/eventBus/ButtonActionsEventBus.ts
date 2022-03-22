import { ActionsEventBus } from '@/data/eventBus/ActionsEventBus';
import { ButtonAction } from '@/data/global/actions';
import { getAdminUrl } from '@/services/ajax/getAdminUrl';
import { onButtonAction } from '@/services/actions/onButtonAction';

export class ButtonActionsEventBus extends ActionsEventBus {
  protected url: string = getAdminUrl(window.MyParcelActions.pathButtonAction);

  public async execute<T extends [string, ButtonAction]>(action: T, parameters: RequestParameters = {}): Promise<void> {
    this.url = action[0];

    const response = await super.doAction(action[1], parameters);

    onButtonAction(response, action[1]);
  }
}

export const buttonActionsEventBus = new ButtonActionsEventBus();
