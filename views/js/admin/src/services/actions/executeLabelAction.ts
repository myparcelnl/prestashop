import { LabelAction } from '@/data/global/actions';
import { ModalData } from '@/composables/context/useModalContext';
import { executeAdminAction } from '@/services/actions/executeAdminAction';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { toArray } from '@/utils/toArray';

type ExecuteLabelAction = (action: LabelAction, labelId: number | number[], modalData?: ModalData) => Promise<void | SuccessResponse>;

export const executeLabelAction: ExecuteLabelAction = async(action, labelIdOrIds, modalData?) => {
  const parameters: RequestParameters = {
    labelIds: toArray<number>(labelIdOrIds),
  };

  return executeAdminAction(labelActionsEventBus, action, parameters, modalData);
};
