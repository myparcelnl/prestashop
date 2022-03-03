/* eslint-disable no-case-declarations */
import { ActionResponse, LabelAction, OrderAction, modifyLabelActions } from '@/data/global/actions';
import { ContextKey } from '@/data/global/context';
import { findLabelIndex } from '@/utils/findLabelIndex';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

/**
 * After refreshing labels, get the new labels and update global shipment labels data.
 */
export function onNewLabels(response: ActionResponse<typeof modifyLabelActions[number]>): void {
  switch (response.action) {
    case LabelAction.DELETE:
      const globalLabelContext = useGlobalContext(ContextKey.SHIPMENT_LABELS);

      response.data.labelIds.forEach((labelId) => {
        const index = findLabelIndex(globalLabelContext, labelId);

        if (index !== -1) {
          globalLabelContext.value.labels.splice(index, 1);
        }
      });
      break;

    case LabelAction.REFRESH:
    case OrderAction.EXPORT:
    case OrderAction.EXPORT_PRINT:
      const labelContext = useGlobalContext(ContextKey.SHIPMENT_LABELS, {
        labels: response.data.shipmentLabels,
        orderId: Number(response.data.shipmentLabels?.[0]?.id_order),
      });

      response.data.shipmentLabels.forEach((newLabel) => {
        newLabel.refreshed_at = new Date().toISOString();

        const existing = findLabelIndex(labelContext, newLabel.id_label);

        if (existing === -1) {
          labelContext.value.labels.push(newLabel);
        } else {
          labelContext.value.labels.splice(existing, 1, newLabel);
        }
      });
      break;
  }
}
