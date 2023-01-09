<template>
  <div class="card">
    <div
      v-if="$slots.header"
      class="card-header">
      <!-- Card header. -->
      <slot name="header">
        {{ translate(title) }}
      </slot>
    </div>

    <div class="card-body">
      <slot />
    </div>

    <div
      v-if="$slots.footer || actions.length"
      class="card-footer d-flex">
      <slot name="footer">
        <ActionButton
          v-for="(action, index) in actions"
          :key="`${index}_${action.id}`"
          :action="action" />
      </slot>
    </div>
  </div>
</template>

<script lang="ts">
import {ActionButton, PdkButtonAction, useTranslate} from '@myparcel/pdk-frontend';
import {PropType, defineComponent} from 'vue';

export default defineComponent({
  name: 'PsCard',

  components: {
    ActionButton,
  },

  props: {
    loading: {
      type: Boolean,
    },

    title: {type: String, default: null},

    /**
     * Available actions on the card.
     */
    actions: {
      type: Array as PropType<PdkButtonAction[]>,
      default: () => [],
    },
  },

  setup: () => {
    return {
      translate: useTranslate(),
    };
  },
});
</script>
