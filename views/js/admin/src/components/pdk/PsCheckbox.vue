<template>
  <div class="md-checkbox">
    <label>
      <slot>{{ translate(label) }}</slot>
      <input
        v-model="model"
        :disabled="disabled"
        :value="value"
        class="form-control"
        type="checkbox" />
      <i class="md-checkbox-control" />
    </label>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {useLanguage} from '@myparcel-pdk/admin/src';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsCheckbox',

  props: {
    disabled: {
      type: Boolean,
    },

    label: {
      type: String,
      default: null,
    },

    value: {
      type: [Boolean, String, Number],
      default: true,
    },

    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Boolean, Array],
      default: null,
    },
  },

  emits: ['update:modelValue'],

  setup: (props, ctx) => {
    return {
      model: useVModel(props, 'modelValue', ctx.emit),
      translate: useLanguage(),
    };
  },
});
</script>
