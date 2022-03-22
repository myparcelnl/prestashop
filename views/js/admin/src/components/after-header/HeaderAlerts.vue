<template>
  <PsAlerts
    id="myparcel-header-alerts"
    :alerts="alerts" />
</template>

<script lang="ts">
import PsAlerts from '@/components/common/PsAlerts.vue';
import { defineComponent } from '@vue/composition-api';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { moduleSettingsEventBus } from '@/data/eventBus/ModuleSettingsEventBus';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';
import { buttonActionsEventBus } from '@/data/eventBus/ButtonActionsEventBus';

export default defineComponent({
  name: 'HeaderAlerts',
  components: { PsAlerts },
  setup: () => {
    return {
      ...useEventBusAlerts(
        [
          labelActionsEventBus,
          orderActionsEventBus,
          moduleSettingsEventBus,
          buttonActionsEventBus,
        ],
        'body',
      ),
    };
  },
});
</script>
