<template>
  <PsCard>
    <template #header>
      MyParcel
    </template>

    <template #default>
      <ConceptCard />
      <ShipmentsCard />
      <PsAlerts :alerts="alerts" />
    </template>
  </PsCard>
</template>

<script lang="ts">
import ConceptCard from '@/components/order/ConceptCard.vue';
import PsAlerts from '@/components/common/PsAlerts.vue';
import PsCard from '@/components/common/PsCard.vue';
import ShipmentsCard from '@/components/order/ShipmentsCard.vue';
import { defineComponent } from '@vue/composition-api';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';

export default defineComponent({
  name: 'OrderGridCard',
  components: {
    ConceptCard,
    PsAlerts,
    PsCard,
    ShipmentsCard,
  },

  setup: () => ({
    ...useEventBusAlerts([
      deliveryOptionsEventBus,
      labelActionsEventBus,
      orderActionsEventBus,
    ]),
  }),
});
</script>
