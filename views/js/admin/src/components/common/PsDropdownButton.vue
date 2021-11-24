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
        {{ $filters.translate(option.label) }}
      </PsDropdownButtonItem>
    </div>
  </PsButton>
</template>

<script lang="ts">
import PsButton from '@/components/common/PsButton.vue';
import PsDropdownButtonItem from '@/components/common/PsDropdownButtonItem.vue';
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useOptionsProps } from '@/composables/props/useOptionsProps';

export default defineComponent({
  name: 'PsDropdownButton',
  components: { PsDropdownButtonItem, PsButton },
  props: {
    ...disabledProps,
    ...useOptionsProps<DropdownButtonItem>(),
  },

  emits: ['click'],
});
</script>
