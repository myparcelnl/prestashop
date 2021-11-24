<template>
  <div class="myparcel-radio-container">
    <input
      :id="`radio_${value}`"
      v-model="mutableValue"
      :value="value"
      type="radio"
      class="myparcel-radio">
    <label
      v-t="label"
      :for="`radio_${value}`" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { labelProps } from '@/composables/props/labelProps';
import { useRadioModel } from '@/composables/props/model/useRadioModel';

const { model, props, setup } = useRadioModel();

export default defineComponent({
  name: 'PsRadio',
  model,
  props: {
    checked: props.checked,
    value: props.value,
    ...disabledProps,
    ...labelProps,
  },

  setup,
});
</script>
