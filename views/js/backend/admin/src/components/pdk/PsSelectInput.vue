<template>
  <select
    :id="id"
    v-model="model"
    v-test="[AdminComponent.SelectInput, element]"
    :name="id"
    class="custom-select form-control">
    <option
      v-for="option in options"
      :key="option.value"
      :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script generic="T extends SelectInputModelValue" lang="ts" setup>
import {
  type SelectInputProps,
  type SelectInputEmits,
  type SelectInputModelValue,
  AdminComponent,
  useSelectInputContext,
} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<SelectInputProps<T>>();
const emit = defineEmits<SelectInputEmits<T>>();

const {id, model, options} = useSelectInputContext(props, emit);
</script>
