<template>
  <div>
    <TransitionGroup
      name="mypa__fade"
      appear>
      <LabelCard
        v-for="label in labels"
        :key="`${label.id_order}_${label.id_order_label}_${label.id_label}`"
        :label="label" />
    </TransitionGroup>

    <div class="btn-group">
      <PsButton
        variant="outline-secondary"
        data-toggle="modal"
        data-target="#shipmentOptions"
        :click-context="{ orderId }">
        <MaterialIcon icon="label" />
        {{ $filters.translate('create') }}
      </PsButton>

      <PsButton
        v-if="labels.length"
        variant="primary"
        @click="print">
        <MaterialIcon icon="print" />
        {{ $filters.translate('print') }}
      </PsButton>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import LabelCard from '@/components/order-list-column/LabelCard.vue';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import { OrderAction } from '@/data/global/actions';
import PsButton from '@/components/common/PsButton.vue';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { useGlobalInstanceContext } from '@/composables/context/useGlobalInstanceContext';

/**
 * The "Labels" column in the orders list.
 *
 * @see /admin1/index.php/sell/orders
 */
export default defineComponent({
  name: 'OrderListColumn',
  components: {
    MaterialIcon,
    LabelCard,
    PsButton,
  },

  setup: () => {
    const shipmentLabelsContext = useGlobalInstanceContext(ContextKey.SHIPMENT_LABELS);
    const shipmentOptionsContext = useGlobalInstanceContext(ContextKey.SHIPMENT_OPTIONS);
    const { orderId } = shipmentOptionsContext.value;

    const labels = ref<ShipmentLabel[]>([]);

    watch(shipmentLabelsContext, (newContext) => {
      labels.value = newContext.labels;
    }, { deep: true, immediate: true });

    return {
      orderId,
      labels,
      print: async(): Promise<void> => {
        await executeOrderAction(OrderAction.PRINT, orderId ?? undefined);
      },
    };
  },
});
</script>
