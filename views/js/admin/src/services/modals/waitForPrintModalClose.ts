import { ContextKey } from '@/data/global/context';
import { ModalData } from '@/composables/context/useModalContext';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { waitForModalClose } from '@/services/modals/waitForModalClose';

/**
 * Instantly returns true if labelPromptForPosition is disabled.
 */
export async function waitForPrintModalClose(modalData: ModalData = null): Promise<boolean> {
  const printContext = useGlobalContext(ContextKey.PRINT_OPTIONS);

  if (!printContext.value.promptForLabelPosition) {
    return true;
  }

  return waitForModalClose('printOptions', modalData);
}
