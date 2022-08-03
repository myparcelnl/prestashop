<template>
  <button
    type="button"
    class="btn text-nowrap"
    :class="{
      [`btn-${size}`]: size,
      [`btn-${variant}`]: variant,
    }"
    :disabled="disabled"
    @click="() => $emit('click')">
    <MaterialIcon
      v-for="iconName in icons"
      :key="iconName"
      class="mr-1"
      >{{ iconName }}</MaterialIcon
    >
    <slot>
      {{ translate(label) }}
    </slot>
  </button>
</template>

<script lang="ts">
import {PropType, computed, defineComponent} from 'vue';
import {toArray, useTranslate} from '@myparcel/pdk-frontend';
import MaterialIcon from '@/components/MaterialIcon.vue';

export default defineComponent({
  name: 'PsButton',
  components: {MaterialIcon},
  props: {
    variant: {
      type: String,
      default: 'primary',
    },

    disabled: {
      type: Boolean,
    },

    size: {
      type: String,
      default: null,
    },

    icon: {
      type: [Array, String] as PropType<string | string[]>,
      default: () => [],
    },

    label: {
      type: String,
      default: 'save',
    },
  },

  emits: ['click'],

  setup: (props) => {
    return {
      translate: useTranslate(),
      icons: computed(() => toArray(props.icon)),
    };
  },
});
</script>
