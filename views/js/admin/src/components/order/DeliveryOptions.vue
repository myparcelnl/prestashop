<template>
  <Transition name="mypa__fade">
    <PsAlerts
      v-if="alerts.length"
      :alerts="alerts" />
    <form v-else-if="loaded">
      <div
        v-show="loaded"
        v-html="htmlContent" />
    </form>
    <LoaderOverlay
      v-else
      :show="true"
      style="height: 100px" />
  </Transition>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import LoaderOverlay from '@/components/common/LoaderOverlay.vue';
import PsAlerts from '@/components/common/PsAlerts.vue';
import { contextProps } from '@/composables/props/contextProps';
import { defineComponent } from '@vue/composition-api';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { useDeliveryOptions } from '@/composables/useDeliveryOptions';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'DeliveryOptions',
  components: { PsAlerts, LoaderOverlay },
  props: {
    ...contextProps,
  },

  setup: () => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS);
    const onUpdate = (event: CustomEvent): void => {
      deliveryOptionsEventBus.update({
        orderId: contextData.value.orderId ?? null,
        deliveryOptions: event.detail,
      });
    };

    return {
      ...useEventBusAlerts(deliveryOptionsEventBus),
      ...useDeliveryOptions(onUpdate),
    };
  },
});
</script>
