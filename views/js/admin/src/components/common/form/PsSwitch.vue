<template>
  <div class="ps-switch">
    <template v-for="(entry, index) in options">
      <input
        :id="`${name}_${entry}_${index}`"
        :key="`${name}_${entry}_${index}_input`"
        v-model="mutableValue"
        :name="`${name}_${entry}`"
        type="radio"
        class="ps-switch"
        :value="Boolean(index)">
      <label
        :key="`${name}_${entry}_${index}_label`"
        :for="`${name}_${entry}_${index}`"
        v-text="entry" />
    </template>
    <span class="slide-button" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useCheckboxModel } from '@/composables/props/model/useCheckboxModel';
import { useOptionsProps } from '@/composables/props/useOptionsProps';

const { model, props, setup } = useCheckboxModel();

export default defineComponent({
  name: 'PsSwitch',
  model,
  props: {
    name: {
      type: String,
      required: true,
    },

    // eslint-disable-next-line vue/no-unused-properties
    checked: props.checked,
    value: props.value,
    disabled: disabledProps.disabled,
    options: useOptionsProps<string>().options,
  },

  setup: (props, ctx) => {
    return {
      ...setup(props, ctx),
    };
  },
});
</script>
