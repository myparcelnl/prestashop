<template>
  <select
    :id="id"
    v-model="model"
    class="custom-select form-control"
    :class="{
      disabled: element.props.options.length === 1 || element.isDisabled || element.isSuspended,
    }">
    <option
      v-for="(item, index) in element.props.options"
      :key="index"
      :value="item.value"
      v-text="item.label" />

    <!--      :disabled="options.length === 1 || disabled" -->
    <!--        :selected="model ? model === item.value || index === 0 : false" -->
  </select>
</template>

<script setup lang="ts">
import {ElementInstance, generateFieldId} from '@myparcel-pdk/admin/src';
import {useVModel} from '@vueuse/core';

const props = defineProps<{
  element: ElementInstance;
  // eslint-disable-next-line vue/no-unused-properties
  modelValue: string | number | null;
}>();

const emit = defineEmits<(event: 'update:modelValue', value: string | number) => void>();

const model = useVModel(props, undefined, emit);
const id = generateFieldId(props.element);
</script>
