<template>
  <div class="md-checkbox">
    <label>
      <slot>{{ translate(label) }}</slot>
      <input
        v-model="model"
        type="checkbox"
        class="form-control"
        :disabled="disabled"
        :value="value" />
      <i class="md-checkbox-control" />
    </label>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {useTranslate} from '@myparcel/pdk-frontend';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsCheckbox',
  props: {
    disabled: {
      type: Boolean,
    },

    label: {
      type: String,
      required: true,
    },

    value: {
      type: [String, Boolean],
      default: true,
    },

    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Boolean],
      default: null,
    },
  },

  setup: (props, ctx) => {
    return {
      model: useVModel(props, 'modelValue', ctx.emit),
      translate: useTranslate(),
    };
  },
});
</script>
