<template>
  <div class="ps-select">
    <select
      class="custom-select form-control"
      :class="{
        disabled: options.length === 1 || disabled,
      }"
      @change="onChange">
      <option
        v-for="(item, index) in options"
        :key="index"
        :disabled="options.length === 1 || disabled"
        :selected="model
          ? model === item.value || index === 0
          : false"
        :value="item.value"
        v-text="item.label" />
    </select>
  </div>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsSelect',
  props: {
    disabled: {
      type: Boolean,
    },

    modelValue: {
      type: [String, Boolean],
      default: null,
    },

    options: {
      type: Array as PropType<{ label: string, value: string }[]>,
      required: true,
    },
  },

  setup: (props, ctx) => {
    const onChange = (event: Event): void => {
      const element = event.target as HTMLSelectElement;
      ctx.emit('change', element.value);
    };

    return {
      model: useVModel(props, 'modelValue', ctx.emit),
      onChange,
    };
  },
});
</script>
