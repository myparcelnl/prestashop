import { ActionResponse, LabelAction, OrderAction, modifyLabelActions } from '@/data/global/actions';
import { ContextKey } from '@/data/global/context';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

/**
 * After refreshing labels, get the new labels and update global shipment labels data.
 */
export function onNewLabels(response: ActionResponse<typeof modifyLabelActions[number]>): void {
  const labelContext = useGlobalContext(ContextKey.SHIPMENT_LABELS);

  switch (response.action) {
    case LabelAction.DELETE:
      response.data.labelIds.forEach((labelId) => {
        const index = labelContext.value.labels.findIndex((label: ShipmentLabel) => label.id_label === labelId);

        if (index !== -1) {
          labelContext.value.labels.splice(index, 1);
        }
      });
      break;

    case LabelAction.REFRESH:
    case OrderAction.EXPORT:
    case OrderAction.EXPORT_PRINT:
      response.data.shipmentLabels.forEach((newLabel) => {
        newLabel.refreshed_at = new Date().toISOString();

        const existing = labelContext.value.labels.findIndex((label) => label.id_label === newLabel.id_label);

        if (existing === -1) {
          labelContext.value.labels.push(newLabel);
        } else {
          labelContext.value.labels.splice(existing, 1, newLabel);
        }
      });
      break;
  }
}
