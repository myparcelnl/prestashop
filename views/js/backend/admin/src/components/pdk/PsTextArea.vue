<template>
  <textarea
    :id="id"
    v-model.trim="model"
    :class="{
      'form-required': !element.isValid,
    }"
    :disabled="element.isDisabled || element.isSuspended"
    class="!mypa-w-full" />
</template>

<script lang="ts" setup>
import {useVModel} from '@vueuse/core';
import {type ElementInstance, generateFieldId} from '@myparcel-pdk/admin';
import {type InteractiveElementInstance} from '@myparcel/vue-form-builder';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<{modelValue: string | number; element: InteractiveElementInstance}>();
const emit = defineEmits<(e: 'update:modelValue', value: string | number) => void>();

const model = useVModel(props, undefined, emit);

const id = generateFieldId(props.element as ElementInstance);
</script>
