<template>
  <PdkSelectInput
    :id="id"
    v-model="model"
    v-test="[AdminComponent.MultiSelectInput, element]"
    :element="element"
    multiple="true" />
</template>

<script generic="T extends MultiSelectInputModelValue" lang="ts" setup>
import {
  type MultiSelectInputProps,
  type MultiSelectInputEmits,
  type MultiSelectInputModelValue,
  AdminComponent,
  useElementContext,
} from '@myparcel-dev/pdk-admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<MultiSelectInputProps<T>>();
const emit = defineEmits<MultiSelectInputEmits<T>>();

const {id, model} = useElementContext(props, emit);
</script>
