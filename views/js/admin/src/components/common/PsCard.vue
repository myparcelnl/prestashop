<template>
  <div class="card">
    <div
      v-if="$slots.header"
      class="card-header">
      <slot name="header" />
    </div>
    <div class="card-body">
      <slot />
    </div>
    <div
      v-if="$slots.footer"
      class="card-footer d-flex">
      <slot name="footer" />
    </div>
    <LoaderOverlay :show="loading" />
  </div>
</template>

<script lang="ts">
import LoaderOverlay from '@/components/common/LoaderOverlay.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PsCard',
  components: { LoaderOverlay },

  props: {
    loading: {
      type: Boolean,
    },
  },
});
</script>
