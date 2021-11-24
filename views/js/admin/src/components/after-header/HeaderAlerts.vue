<template>
  <PsAlerts
    id="myparcel-header-alerts"
    :alerts="alerts" />
</template>

<script lang="ts">
import PsAlerts from '@/components/common/PsAlerts.vue';
import { defineComponent } from '@vue/composition-api';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';

export default defineComponent({
  name: 'HeaderAlerts',
  components: { PsAlerts },
  setup: () => {
    return {
      ...useEventBusAlerts(
        [
          labelActionsEventBus,
          orderActionsEventBus,
        ],
        'body',
      ),
    };
  },
});
</script>
