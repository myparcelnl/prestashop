<template>
  <BaseButton
    :icon="icon"
    :label="label"
    :disabled="disabled"
    class="btn btn-primary text-nowrap"
    @click="$emit('click')">
    <slot />
  </BaseButton>
</template>

<script lang="ts">
/* eslint-disable vue/no-unused-properties */
import {PropType, defineComponent} from 'vue';
import BaseButton from '../BaseButton.vue';
import {PdkIcon} from '@myparcel/pdk-frontend';

export default defineComponent({
  name: 'PsButton',
  components: {BaseButton},
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
});
</script>
