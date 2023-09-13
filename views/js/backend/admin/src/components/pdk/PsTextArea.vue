<template>
  <textarea
    :id="id"
    v-model.trim="model"
    v-test="AdminComponent.TextArea"
    :class="{
      'form-required': !element.isValid,
    }"
    :disabled="element.isDisabled || element.isSuspended"
    class="!mypa-w-full" />
</template>

<script lang="ts" setup>
import {AdminComponent, type TextAreaProps, type TextAreaEmits, useElementContext} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<TextAreaProps>();
const emit = defineEmits<TextAreaEmits>();

const {id, model} = useElementContext(props, emit);
</script>
