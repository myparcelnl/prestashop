import { ActionResponse, AdminAction, OrderAction, modifyLabelActions, printActions } from '@/data/global/actions';
import { ActionsEventBus } from '@/data/eventBus/ActionsEventBus';
import { ContextKey } from '@/data/global/context';
import { ModalData } from '@/composables/context/useModalContext';
import { isInArray } from '@/utils/type-guard/isInArray';
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
): Promise<void | SuccessResponse> {
  const callbacks: ((res: ActionResponse) => void)[] = [];
  const requestParameters: RequestParameters = { action, ...parameters };

  if (OrderAction.CREATE_RETURN_LABEL === action) {
    if (!await waitForReturnsFormModalClose(modalData)) {
      return;
    }

    const returnOptionsContext = useGlobalContext(ContextKey.RETURNS_FORM);
    requestParameters.labelDescription = returnOptionsContext.value.labelDescription;
    requestParameters.packageType = returnOptionsContext.value.packageType;
    requestParameters.packageFormat = returnOptionsContext.value.packageFormat;
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

    callbacks.push(onPrintLabels as ((res: ActionResponse) => void));
  }

  if (isInArray(action, modifyLabelActions)) {
    callbacks.push(onNewLabels as ((res: ActionResponse) => void));
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
