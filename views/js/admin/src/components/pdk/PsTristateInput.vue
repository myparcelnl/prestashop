<template>
  <select
    :id="id"
    v-model="model"
    :class="{
      disabled: options.length === 1 || element.isDisabled || element.isSuspended,
    }"
    :name="id"
    class="custom-select form-control">
    <option
      v-for="(item, index) in options"
      :key="index"
      :disabled="item.disabled"
      :value="item.value"
      v-text="item.label" />
  </select>
</template>

<script
  lang="ts"
  setup>
import { type ElementInstance, type OptionsProp, useSelectInputContext } from '@myparcel-pdk/frontend-admin-core'; // eslint-disable-next-line vue/no-unused-properties

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<{ element: ElementInstance<OptionsProp>; modelValue: string | number }>();
const emit = defineEmits<(e: 'update:modelValue', value: string | number) => void>();

const options = [
  {
    label: 'Yes',
    value: 1,
  },
  {
    label: 'No',
    value: 0,
  },
  {
    label: 'Default',
    value: -1,
  },
];
const {id, model} = useSelectInputContext(props, emit);
</script>
