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
        :selected="mutableValue
          ? mutableValue === item.value || index === 0
          : false"
        :value="item.value"
        v-text="item.label" />
    </select>
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useOptionsProps } from '@/composables/props/useOptionsProps';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'PsSelect',
  model,
  props: {
    ...props,
    ...disabledProps,
    ...useOptionsProps(),
  },

  setup: (props, ctx) => {
    const onChange = (event: Event): void => {
      const element = event.target as HTMLSelectElement;
      ctx.emit('change', element.value);
    };

    return {
      ...setup(props, ctx),
      onChange,
    };
  },
});
</script>
