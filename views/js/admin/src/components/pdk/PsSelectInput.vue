<template>
  <select
    v-model="model"
    class="custom-select form-control"
    :class="{
      disabled: options.length === 1 || disabled,
    }">
    <option
      v-for="(item, index) in options"
      :key="index"
      :value="item.value"
      v-text="item.label" />

    <!--      :disabled="options.length === 1 || disabled" -->
    <!--        :selected="model ? model === item.value || index === 0 : false" -->
  </select>
</template>

<script lang="ts">
import {PropType, defineComponent} from 'vue';
import {SelectOption} from '@myparcel/pdk-frontend';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsSelectInput',

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

    /**
     * The options of the select.
     */
    options: {
      type: Array as PropType<SelectOption[]>,
      default: (): SelectOption[] => [],
    },
  },

  emits: ['change', 'update:modelValue'],

  setup: (props, ctx) => {
    return {
      model: useVModel(props, 'modelValue', ctx.emit),
    };
  },
});
</script>
