<template>
  <component :is="component">
    <slot />
  </component>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PsTableCol',
  props: {
    component: {
      type: String,
      default: 'td',
    },
  },
});
</script>
