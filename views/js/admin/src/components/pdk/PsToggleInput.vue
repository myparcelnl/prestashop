<template>
  <div class="input-container">
    <div class="input-group">
      <span class="ps-switch">
        <input
          :id="`${id}_no`"
          :checked="model === false"
          class="ps-switch"
          type="radio"
          value="0"
          @change="model = false" />
        <label
          :for="`${id}_no`"
          v-text="translate('no')" />

        <input
          :id="`${id}_yes`"
          :checked="model === true"
          class="ps-switch"
          type="radio"
          value="1"
          @change="model = true" />
        <label
          :for="`${id}_yes`"
          v-text="translate('yes')" />

        <span class="slide-button" />
      </span>
    </div>

    <!--    <small class="form-text">Enable suppliers page on your front office even when its module is disabled.</small> -->
  </div>
</template>

<script lang="ts" setup>
import {ElementInstance, generateFieldId, useLanguage} from '@myparcel-pdk/admin';
import {InteractiveElementInstance} from '@myparcel/vue-form-builder';
import {useVModel} from '@vueuse/core';

// TODO: refactor to have one input component. Check WooCommerce for example.

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<{modelValue: boolean; element: InteractiveElementInstance}>();
const emit = defineEmits<(e: 'update:modelValue', value: boolean) => void>();

const model = useVModel(props, undefined, emit);

const id = generateFieldId(props.element as ElementInstance);

const {translate} = useLanguage();
</script>
