<template>
  <button
    type="button"
    :disabled="disabled"
    @click="$emit('click')">
    <PdkIcon
      v-if="icon"
      class="mr-1"
      :icon="icon" />
    <slot>
      {{ translate(label) }}
    </slot>
  </button>
</template>

<script lang="ts">
/* eslint-disable vue/no-unused-properties */
import {PdkIcon, useTranslate} from '@myparcel/pdk-frontend';
import {PropType, defineComponent} from 'vue';

export default defineComponent({
  name: 'BaseButton',
  props: {
    disabled: {
      type: Boolean,
    },

    icon: {
      type: String as PropType<PdkIcon>,
      default: null,
    },

    label: {
      type: String,
      default: null,
    },
  },

  emits: ['click'],

  setup: () => ({
    translate: useTranslate(),
  }),
});
</script>
