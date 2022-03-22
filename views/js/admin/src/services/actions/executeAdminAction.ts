import { ActionResponse, AdminAction, LabelAction, modifyLabelActions, printActions } from '@/data/global/actions';
import { ActionsEventBus } from '@/data/eventBus/ActionsEventBus';
import { ButtonActionsEventBus } from '@/data/eventBus/ButtonActionsEventBus';
import { ContextKey } from '@/data/global/context';
import { ModalData } from '@/composables/context/useModalContext';
import { isInArray } from '@/utils/type-guard/isInArray';
import { onButtonAction } from '@/services/actions/onButtonAction';
import { onNewLabels } from '@/services/actions/onNewLabels';
import { onPrintLabels } from '@/services/actions/onPrintLabels';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { usePdfWindow } from '@/composables/usePdfWindow';
import { waitForPrintModalClose } from '@/services/modals/waitForPrintModalClose';
import { waitForReturnsFormModalClose } from '@/services/modals/waitForReturnsFormModalClose';

/**
 * Execute a label action. If it's a print action the label position prompt will be shown, if enabled.
 */
export async function executeAdminAction(
  eventBus: ActionsEventBus,
  action: AdminAction,
  parameters: RequestParameters = {},
  modalData: ModalData = null,
): Promise<void | SuccessResponse & ActionResponse> {
  const callbacks: ((res: ActionResponse) => void)[] = [];
  const requestParameters: RequestParameters = { action, ...parameters };

  if (LabelAction.CREATE_RETURN_LABEL === action && !await waitForReturnsFormModalClose(modalData)) {
    return;
  }

  if (isInArray(action, printActions)) {
    if (!await waitForPrintModalClose(modalData)) {
      return;
    }

    const printOptionsContext = useGlobalContext(ContextKey.PRINT_OPTIONS);
    requestParameters.labelFormat = printOptionsContext.value.labelFormat;
    requestParameters.labelPosition = printOptionsContext.value.labelPosition;
    requestParameters.labelOutput = printOptionsContext.value.labelOutput;

    if (printOptionsContext.value.labelOutput === 'open') {
      await usePdfWindow().open();
    }

    callbacks.push(onPrintLabels);
  }

  if (isInArray(action, modifyLabelActions)) {
    callbacks.push(onNewLabels);
  }

  if (isInArray(action, modifyLabelActions)) {
    callbacks.push(onNewLabels);
  }

  const response = await eventBus.doAction(action, requestParameters);

  if (!response || !callbacks.length) {
    return response;
  }

  callbacks.forEach((callback) => {
    callback(response);
  });

  return response;
}
