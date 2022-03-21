import { ActionsEventBus } from '@/data/eventBus/ActionsEventBus';
import { getLabelControllerUrl } from '@/data/eventBus/GetLabelControllerUrl';

class LabelActionsEventBus extends ActionsEventBus {
  protected url: string = getLabelControllerUrl();
}

export const labelActionsEventBus = new LabelActionsEventBus();
