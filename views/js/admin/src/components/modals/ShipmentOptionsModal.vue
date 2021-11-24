<template>
  <Modal
    :context-key="$ContextKey.SHIPMENT_OPTIONS"
    title="shipment_options_title"
    save-label="export"
    :on-save="exportOrder">
    <template #default="data">
      <ShipmentOptions v-bind="data" />
    </template>
  </Modal>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import Modal from '@/components/Modal.vue';
import { OrderAction } from '@/data/global/actions';
import ShipmentOptions from '@/components/order/ShipmentOptions.vue';
import { defineComponent } from '@vue/composition-api';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'ShipmentOptionsModal',
  components: {
    Modal,
    ShipmentOptions,
  },

  setup: () => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS);

    return {
      exportOrder: async(): Promise<void> => {
        await executeOrderAction(OrderAction.EXPORT, contextData.value.orderId ?? undefined);
      },
    };
  },
});
</script>
