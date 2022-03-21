<template>
  <PsButton
    icon="create"
    size="sm"
    data-toggle="modal"
    data-target="#deliveryOptions"
    :click-context="{ orderId: contextData.orderId }">
    {{ contextData.deliveryOptions.carrier }} | {{ $filters.formatDate(contextData.deliveryOptions.date) }}
  </PsButton>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import PsButton from '@/components/common/PsButton.vue';
import { contextProps } from '@/composables/props/contextProps';
import { defineComponent } from '@vue/composition-api';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'DeliveryMomentSelector',
  components: { PsButton },
  props: {
    ...contextProps,
  },

  setup: () => ({
    contextData: useGlobalContext(ContextKey.SHIPMENT_OPTIONS),
  }),
});
</script>
