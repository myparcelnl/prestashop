<template>
  <PsButton
    class="btn-sm dropdown-toggle"
    data-toggle="dropdown"
    aria-haspopup="true"
    aria-expanded="false"
    :disabled="disabled">
    <slot>
      <span
        v-t="'toggle_dropdown'"
        class="sr-only" />
    </slot>
    <div class="dropdown-menu dropdown-menu-right">
      <PsDropdownButtonItem
        v-for="(option, index) in options"
        :key="`${index}_${option.label}`"
        :icon="option.icon"
        :variant="option.variant"
        @click="() => $emit('click', option.action)">
        {{ translate(option.label) }}
      </PsDropdownButtonItem>
    </div>
  </PsButton>
</template>

<script lang="ts">
import {DropdownButtonItem, useTranslate} from '@myparcel/pdk-frontend';
import {PropType, defineComponent} from 'vue';
import PsButton from '@/components/pdk/PsButton.vue';
import PsDropdownButtonItem from '@/components/pdk/PsDropdownButtonItem.vue';

export default defineComponent({
  name: 'PsDropdownButton',
  components: {PsDropdownButtonItem, PsButton},
  props: {
    disabled: {
      type: Boolean,
    },

    options: {
      type: Array as PropType<DropdownButtonItem[]>,
      default: () => [],
    },
  },

  emits: ['click'],

  setup: () => {
    return {
      translate: useTranslate(),
    };
  },
});
</script>
