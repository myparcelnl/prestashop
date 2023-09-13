<template>
  <div class="input-container">
    <div class="input-group">
      <span class="ps-switch">
        <input
          :id="id"
          v-model="model"
          :disabled="element.isDisabled || element.isSuspended || element.isReadOnly"
          :readonly="element.isReadOnly"
          :value="true"
          tabindex="-1"
          type="checkbox" />

        <label
          :for="id"
          class="!mypa-opacity-100"
          role="label">
          {{ translate(`toggle_${model ? 'yes' : 'no'}`) }}
        </label>

        <span class="slide-button" />
      </span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import {useLanguage, type PdkElementProps, type PdkElementEmits, useElementContext} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<PdkElementProps<boolean>>();
const emit = defineEmits<PdkElementEmits<boolean>>();

const {id, model} = useElementContext(props, emit);

const {translate} = useLanguage();
</script>
