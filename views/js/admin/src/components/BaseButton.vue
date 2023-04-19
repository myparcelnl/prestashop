<template>
  <button
    :disabled="disabled"
    type="button"
    @click="$emit('click')">
    <PdkIcon
      v-if="icon"
      :icon="icon"
      class="mr-1" />
    <slot>
      {{ translate(label) }}
    </slot>
  </button>
</template>

<script lang="ts">
/* eslint-disable vue/no-unused-properties */
import {AdminIcon, useLanguage} from '@myparcel-pdk/admin/src';
import {PropType, defineComponent} from 'vue';

export default defineComponent({
  name: 'BaseButton',
  props: {
    disabled: {
      type: Boolean,
    },

    icon: {
      type: String as PropType<AdminIcon>,
      default: null,
    },

    label: {
      type: String,
      default: null,
    },
  },

  emits: ['click'],

  setup: () => ({
    translate: useLanguage(),
  }),
});
</script>
