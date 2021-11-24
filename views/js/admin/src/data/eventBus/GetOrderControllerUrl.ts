import { getAdminUrl } from '@/services/ajax/getAdminUrl';

/**
 * Returns the url to AdminMyParcelLabelController.
 */
export function getOrderControllerUrl(): string {
  return getAdminUrl(window.MyParcelActions.pathOrder);
}
