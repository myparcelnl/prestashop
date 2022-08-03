<template>
  <div class="myparcel-radio-container">
    <input
      :id="`radio_${value}`"
      v-model="model"
      :value="value"
      type="radio"
      class="myparcel-radio" />
    <label
      :for="`radio_${value}`"
      v-text="translate(label)" />
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {useTranslate} from '@myparcel-pdk/admin/src';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsRadioInput',

  props: {
    disabled: {
      type: Boolean,
    },

    label: {
      type: String,
      default: '',
    },

    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Number],
      default: null,
    },

    value: {
      type: [String, Number],
      required: true,
    },
  },

  emits: ['update:modelValue'],

  setup: (props, ctx) => ({
    translate: useTranslate(),
    model: useVModel(props, 'modelValue', ctx.emit),
  }),
});
</script>
