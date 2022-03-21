<template>
  <Modal
    :context-key="$ContextKey.SHIPMENT_OPTIONS"
    title="shipment_options_title"
    save-label="export"
    :on-save="exportOrder"
    :force-loading="loading">
    <template #default="data">
      <ShipmentOptions v-bind="data" />
    </template>
  </Modal>
</template>

<script lang="ts">
import Modal from '@/components/modals/Modal.vue';
import { OrderAction } from '@/data/global/actions';
import ShipmentOptions from '@/components/forms/ShipmentOptionsForm.vue';
import { ShipmentOptionsContext } from '@/data/global/context';
import { defineComponent } from '@vue/composition-api';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { shipmentOptionsContextEventBus } from '@/data/eventBus/ShipmentOptionsContextEventBus';
import { useEventBusLoadingState } from '@/composables/useEventBusLoadingState';

/**
 * Shipment options modal. Opened by clicking the "Create" button in the "Labels" column in the orders list.
 */
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
