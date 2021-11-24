<template>
  <span
    class="dropdown-item"
    :class="`text-${variant}`"
    @click.prevent="() => $emit('click', value)">
    <MaterialIcon
      v-if="icon"
      :class="{
        [`text-${variant}`]: variant,
      }"
      :icon="icon" />
    <slot />
  </span>
</template>

<script lang="ts">
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import { defineComponent } from '@vue/composition-api';
import { variantProps } from '@/composables/props/variantProps';

export default defineComponent({
  name: 'PsDropdownButtonItem',
  components: { MaterialIcon },
  props: {
    ...variantProps(),
    icon: {
      type: String,
      default: null,
    },

    value: {
      type: [String, Number],
      default: null,
    },
  },

  emits: ['click'],
});
</script>
