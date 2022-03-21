import { ContextKey } from '@/data/global/context';
import { ModalData } from '@/composables/context/useModalContext';
import { waitForModalClose } from '@/services/modals/waitForModalClose';

/**
 * Waits for user to fill out (or cancel) the returns form.
 */
export async function waitForReturnsFormModalClose(modalData?: ModalData): Promise<boolean> {
  return waitForModalClose(ContextKey.RETURNS_FORM, modalData);
}
