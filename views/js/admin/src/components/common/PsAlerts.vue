<template>
  <div>
    <PsAlert
      v-for="(alert, index) in alerts"
      :key="`alert_${index}_${alert.content}`"
      :variant="alert.variant"
      :content="alert.content" />
  </div>
</template>

<script lang="ts">
import PsAlert from '@/components/common/PsAlert.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PsAlerts',
  components: { PsAlert },
  props: {
    alerts: {
      type: Array,
      default: (): AlertData[] => [],
    },
  },
});
</script>
