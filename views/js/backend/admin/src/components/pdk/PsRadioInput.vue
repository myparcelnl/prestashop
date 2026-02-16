<template>
  <div
    v-test="[AdminComponent.RadioInput, element]"
    class="form-check form-check-radio radio">
    <label class="form-check-label">
      <input
        :id="id"
        v-model="model"
        :disabled="element.isDisabled || element.isSuspended || element.isReadOnly"
        :name="id"
        :readonly="element.isReadOnly"
        :value="element.props?.value"
        type="radio"
        v-bind="$attrs" />

      <PdkIcon
        v-if="element.props?.icon"
        :icon="element.props.icon"
        class="mr-2" />

      <PdkImage
        v-if="element.props?.image"
        :alt="element.label"
        :src="element.props.image"
        class="mr-2"
        width="24" />

      <i class="form-check-round" />

      {{ element.label }}
    </label>
  </div>
</template>

<script lang="ts">
export default {inheritAttrs: false};
</script>

<script lang="ts" setup>
import {AdminComponent, useElementContext, type RadioInputProps, type RadioInputEmits} from '@myparcel-dev/pdk-admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<RadioInputProps>();
const emit = defineEmits<RadioInputEmits>();

const {id, model} = useElementContext(props, emit);
</script>
