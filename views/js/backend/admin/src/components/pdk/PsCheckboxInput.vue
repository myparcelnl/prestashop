<template>
  <div class="checkbox">
    <div class="md-checkbox md-checkbox-inline">
      <label>
        <input
          :id="id"
          v-model="model"
          v-test="[AdminComponent.CheckboxInput, element]"
          :disabled="element.isDisabled || element.isSuspended || element.isReadOnly"
          :name="id"
          :readonly="element.isReadOnly"
          :value="element?.props?.value ?? '1'"
          type="checkbox" />
        <i class="md-checkbox-control"></i>

        {{ element.label }}
      </label>
    </div>
  </div>
</template>

<script generic="T extends CheckboxInputModelValue" lang="ts" setup>
import {
  AdminComponent,
  useElementContext,
  type CheckboxInputProps,
  type CheckboxInputEmits,
  type CheckboxInputModelValue,
} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<CheckboxInputProps<T>>();
const emit = defineEmits<CheckboxInputEmits<T>>();

const {id, model} = useElementContext(props, emit);
</script>
