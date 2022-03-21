import { getAdminUrl } from '@/services/ajax/getAdminUrl';

/**
 * Returns the url to AdminMyParcelOrderController.
 */
export function getLabelControllerUrl(): string {
  return getAdminUrl(window.MyParcelActions.pathLabel);
}
