<template>
  <div>
    <HeaderAlerts />
    <Modals />
  </div>
</template>

<script lang="ts">
import HeaderAlerts from '@/components/after-header/HeaderAlerts.vue';
import Modals from '@/components/modals/Modals.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'AfterHeader',
  components: { HeaderAlerts, Modals },
});
</script>
