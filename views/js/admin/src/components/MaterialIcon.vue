<template>
  <div class="material-icons">
    <slot>
      {{ icon }}
    </slot>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'MaterialIcon',
  props: {
    icon: {
      type: String,
      default: null,
    },
  },
});
</script>
