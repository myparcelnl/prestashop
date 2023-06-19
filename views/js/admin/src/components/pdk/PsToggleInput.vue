<template>
  <div class="input-container">
    <div class="input-group">
      <span class="ps-switch">
        <input
          :id="id"
          v-model="model"
          :disabled="element.isDisabled || element.isSuspended"
          :value="true"
          tabindex="-1"
          type="checkbox" />

        <label
          role="label"
          :for="id"
          class="!mypa-opacity-100">
          {{ translate(`toggle_${model ? 'yes' : 'no'}`) }}
        </label>

        <span class="slide-button" />
      </span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import {useVModel} from '@vueuse/core';
import {type ElementInstance, generateFieldId, useLanguage} from '@myparcel-pdk/admin';
import {type InteractiveElementInstance} from '@myparcel/vue-form-builder';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<{modelValue: boolean; element: InteractiveElementInstance}>();
const emit = defineEmits<(e: 'update:modelValue', value: boolean) => void>();

const model = useVModel(props, undefined, emit);

const id = generateFieldId(props.element as ElementInstance);

const {translate} = useLanguage();
</script>
