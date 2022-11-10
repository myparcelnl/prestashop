<template>
  <div class="myparcel-radio-container">
    <input
      :id="`radio_${value}`"
      v-model="model"
      :value="value"
      type="radio"
      class="myparcel-radio">
    <label
      v-t="label"
      :for="`radio_${value}`" />
  </div>
</template>

<script lang="ts">
import {useVModel} from '@vueuse/core';
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'PsRadio',

  props: {
    modelValue: {
      type: [String, Number],
      default: null,
    },

    value: {
      type: [String, Number],
      required: true,
    },

    disabled: {
      type: Boolean,
      default: false,
    },

    label: {
      type: String,
      default: '',
    },
  },

  setup: (props, ctx) => ({
    model: useVModel(props, 'modelValue', ctx.emit),
  }),
});
</script>
