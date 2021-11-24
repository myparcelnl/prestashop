<template>
  <PsAlerts :alerts="notices" />
</template>

<script lang="ts">
import PsAlerts from '@/components/common/PsAlerts.vue';
import { defineComponent } from '@vue/composition-api';
import { useNotices } from '@/composables/useNotices';

export default defineComponent({
  name: 'Notices',
  components: { PsAlerts },
  setup: () => {
    const { notices } = useNotices();

    return { notices };
  },
});
</script>
