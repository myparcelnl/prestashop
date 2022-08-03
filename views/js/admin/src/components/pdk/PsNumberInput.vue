<template>
  <input
    v-model="model"
    type="number"
    :disabled="disabled"
    class="border px-3 py-2 rounded" />
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsNumberInput',

  props: {
    /**
     * Controls disabled state.
     */
    disabled: {
      type: Boolean,
    },

    /**
     * The value of the model.
     */
    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Number],
      default: null,
    },
  },

  emits: ['update:modelValue'],

  setup: (props, ctx) => ({
    model: useVModel(props, 'modelValue', ctx.emit),
  }),
});
</script>
