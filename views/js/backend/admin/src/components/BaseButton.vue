<template>
  <button
    :disabled="disabled"
    type="button"
    @click="$emit('click')">
    <PdkIcon
      v-if="icon"
      :icon="icon"
      class="mr-1" />
    <slot>
      {{ translate(label) }}
    </slot>
  </button>
</template>

<script lang="ts" setup>
import {type PropType} from 'vue';
import {type Size, type AdminIcon, useLanguage} from '@myparcel-dev/pdk-admin';

defineProps({
  disabled: {
    type: Boolean,
  },

  icon: {
    type: String as PropType<AdminIcon>,
    default: null,
  },

  label: {
    type: String,
    default: null,
  },

  loading: {
    type: Boolean,
  },

  size: {
    type: String as PropType<Size>,
    default: 'md',
  },
});

defineEmits<(event: 'click') => void>();

const {translate} = useLanguage();
</script>
