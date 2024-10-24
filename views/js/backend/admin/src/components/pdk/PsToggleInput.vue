<template>
  <div class="input-container">
    <input
      :id="id"
      v-model="model"
      :name="id"
      type="hidden" />

    <div class="input-group">
      <span class="ps-switch">
        <input
          :id="toggleId"
          v-model="toggleModel"
          :disabled="element.isDisabled || element.isSuspended || element.isReadOnly"
          :readonly="element.isReadOnly"
          :value="true"
          tabindex="-1"
          type="checkbox" />

        <label
          :for="toggleId"
          class="!mypa-opacity-100"
          role="label">
          {{ translate(`toggle_${toggleModel ? 'yes' : 'no'}`) }}
        </label>

        <span class="slide-button" />
      </span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import {useLanguage, type ToggleInputProps, type ToggleInputEmits, useToggleInputContext} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<ToggleInputProps>();
const emit = defineEmits<ToggleInputEmits>();

const {id, model, toggleId, toggleModel} = useToggleInputContext(props, emit);

const {translate} = useLanguage();
</script>
