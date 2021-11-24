<template>
  <div>
    <Notices />
    <Modals />
  </div>
</template>

<script lang="ts">
import Modals from '@/components/modals/Modals.vue';
import Notices from '@/components/Notices.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'AfterHeader',
  components: { Notices, Modals },
});
</script>
