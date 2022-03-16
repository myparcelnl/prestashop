<template>
  <Modal
    :context-key="$ContextKey.SHIPMENT_OPTIONS"
    title="shipment_options_title"
    save-label="export"
    :on-save="exportOrder"
    :loading="loading">
    <template #default="data">
      <ShipmentOptions v-bind="data" />
    </template>
  </Modal>
</template>

<script lang="ts">
import Modal from '@/components/Modal.vue';
import { OrderAction } from '@/data/global/actions';
import ShipmentOptions from '@/components/order/ShipmentOptions.vue';
import { ShipmentOptionsContext } from '@/data/global/context';
import { defineComponent } from '@vue/composition-api';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { shipmentOptionsContextEventBus } from '@/data/eventBus/ShipmentOptionsContextEventBus';
import { useEventBusLoadingState } from '@/composables/useEventBusLoadingState';

export default defineComponent({
  name: 'ShipmentOptionsModal',
  components: {
    Modal,
    ShipmentOptions,
  },

  setup: () => {
    return {
      ...useEventBusLoadingState(shipmentOptionsContextEventBus),
      exportOrder: async(id: string, context: ShipmentOptionsContext): Promise<void> => {
        await executeOrderAction(OrderAction.EXPORT, context.orderId ?? undefined);
      },
    };
  },
});
</script>
