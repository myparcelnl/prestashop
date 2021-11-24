<template>
  <button
    type="button"
    class="btn"
    :class="{
      [`btn-${size}`]: size,
      [`btn-${variant}`]: variant,
    }"
    :disabled="disabled"
    :data-context="encodedContextData"
    @click="() => $emit('click')">
    <MaterialIcon
      v-for="iconName in icons"
      :key="iconName"
      class="mr-1"
      v-text="iconName" />
    <slot>
      {{ $filters.translate(label) }}
    </slot>
  </button>
</template>

<script lang="ts">
import { PropType, computed, defineComponent } from '@vue/composition-api';
import { AnyContext } from '@/data/global/context';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import { disabledProps } from '@/composables/props/disabledProps';
import { toArray } from '@/utils/toArray';
import { variantProps } from '@/composables/props/variantProps';

export default defineComponent({
  name: 'PsButton',
  components: { MaterialIcon },
  props: {
    ...variantProps('primary'),
    ...disabledProps,
    clickContext: {
      type: Object as PropType<Partial<AnyContext>>,
      default: null,
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

  setup: (props) => ({
    icons: computed(() => toArray(props.icon)),

    encodedContextData: computed((): string | null => {
      return props.clickContext ? JSON.stringify(props.clickContext) : null;
    }),
  }),
});
</script>
