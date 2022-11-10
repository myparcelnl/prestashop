<template>
  <div>
    <slot>
      item
    </slot>
  </div>
</template>

<script lang="ts">
import { useTranslate } from '@myparcel/pdk-frontend';
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'PsDropdownButtonItem',
  props: {},

  emits: ['click'],

  setup: () => {
    return {
      translate: useTranslate(),
    };
  },
});
</script>
