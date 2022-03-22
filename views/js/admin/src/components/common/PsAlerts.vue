<template>
  <TransitionGroup
    name="mypa__fade"
    appear>
    <PsAlert
      v-for="(alert, index) in alerts"
      :key="`alert_${index}_${alert.content}`"
      :variant="alert.variant"
      :content="alert.content" />
  </TransitionGroup>
</template>

<script lang="ts">
import { PropType, defineComponent } from '@vue/composition-api';
import PsAlert from '@/components/common/PsAlert.vue';

export default defineComponent({
  name: 'PsAlerts',
  components: { PsAlert },
  props: {
    alerts: {
      type: Array as PropType<AlertData[]>,
      default: (): AlertData[] => [],
    },
  },
});
</script>
