<template>
  <div class="md-checkbox">
    <label>
      <slot>{{ $filters.translate(label) }}</slot>
      <input
        v-model="mutableValue"
        type="checkbox"
        class="form-control"
        :disabled="disabled"
        :value="value">
      <i class="md-checkbox-control" />
    </label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useCheckboxModel } from '@/composables/props/model/useCheckboxModel';

const { model, props, setup } = useCheckboxModel();

export default defineComponent({
  name: 'PsCheckbox',
  model,
  props: {
    checked: props.checked,
    value: props.value,
    ...disabledProps,
    label: {
      type: String,
      default: null,
    },
  },

  setup,
});
</script>
