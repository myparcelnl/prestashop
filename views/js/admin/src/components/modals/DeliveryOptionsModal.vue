<template>
  <Modal
    context-key="deliveryOptions"
    title="delivery_options_title"
    :on-save="onSave">
    <template #default="data">
      <DeliveryOptions v-bind="data" />
    </template>
  </Modal>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import DeliveryOptions from '@/components/order-card/DeliveryOptions.vue';
import Modal from '@/components/modals/Modal.vue';
import { ModalCallback } from '@/composables/context/useModalContext';
import { defineComponent } from '@vue/composition-api';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { shipmentOptionsContextEventBus } from '@/data/eventBus/ShipmentOptionsContextEventBus';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

/**
 * Modal used in the single order view to edit delivery options for the order.
 */
export default defineComponent({
  name: 'DeliveryOptionsModal',
  components: {
    Modal,
    DeliveryOptions,
  },

  setup: () => {
    const onSave: ModalCallback = async() => {
      const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS);
      await deliveryOptionsEventBus.saveConfiguration();

      // Refresh global shipment options context
      if (contextData.value.orderId) {
        const response = await shipmentOptionsContextEventBus.refresh(contextData.value.orderId);
        contextData.value = response?.data?.context;
      }
    };

    return { onSave };
  },
});
</script>
