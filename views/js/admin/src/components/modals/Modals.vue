<template>
  <div>
    <DeliveryOptionsModal v-if="showDeliveryOptionsModal" />
    <PrintOptionsModal v-if="showPrintModal" />
    <ReturnsFormModal v-if="showReturnsFormModal" />
    <ShipmentOptionsModal v-if="showShipmentOptionsModal" />
  </div>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import { defineComponent } from '@vue/composition-api';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'Modals',
  components: {
    /* eslint-disable @typescript-eslint/naming-convention */
    DeliveryOptionsModal: async() => import('@/components/modals/DeliveryOptionsModal.vue'),
    PrintOptionsModal: async() => import('@/components/modals/PrintOptionsModal.vue'),
    ReturnsFormModal: async() => import('@/components/modals/ReturnsFormModal.vue'),
    ShipmentOptionsModal: async() => import('@/components/modals/ShipmentOptionsModal.vue'),
    /* eslint-enable @typescript-eslint/naming-convention */
  },

  setup: () => {
    const printContext = useGlobalContext(ContextKey.PRINT_OPTIONS);

    return {
      showDeliveryOptionsModal: true,
      showPrintModal: printContext.value.promptForLabelPosition,
      showReturnsFormModal: true,
      showShipmentOptionsModal: true,
    };
  },
});
</script>
