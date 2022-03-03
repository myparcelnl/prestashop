import { ContextKey, MyParcelContext } from '@/data/global/context';
import { WritableComputedRef } from '@vue/composition-api';

/**
 * Compare labels in a ShipmentLabelsContext object by label id.
 */
export function findLabelIndex(
  context: WritableComputedRef<MyParcelContext<ContextKey.SHIPMENT_LABELS>>,
  labelId: string,
): number {
  return context.value.labels.findIndex((label) => {
    return Number(label.id_label) === Number(labelId);
  });
}
